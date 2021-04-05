<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use DateTime;
use GlobalPayments\Api\Entities\AdditionalTaxDetails;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\CommercialLineItem;
use GlobalPayments\Api\Entities\DiscountDetails;
use GlobalPayments\Api\Entities\Enums\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\CreditDebitIndicator;
use GlobalPayments\Api\Entities\Enums\TaxCategory;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

final class CommercialCardTest extends TestCase {
    public function setup() : void {
        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new PorticoConfig;
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        return $config;
    }

    public function testCommercialData1() { // test sending LVL2 data with the orinal transaction
        $commercialData = new CommercialData(TaxType::SALES_TAX);
        $commercialData->taxAmount = '1.23';
        $commercialData->poNumber = '654564564';

        $response = $this->card->charge(112.34)
            ->withAllowDuplicates(true)
            ->withCommercialData($commercialData)
            ->withCurrency('USD')
            ->execute();

        $this->assertEquals('B', $response->commercialIndicator);
    }

    public function test02VisaLevel3() {
        $response = $this->getVisaManual()->charge(112.34) // amount results in commercialIndicator val of 'B'
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withCommercialRequest(true)
            ->withAddress($this->getAddy())
            ->execute();

        if ($response->commercialIndicator === 'B') {
            $commercialData = new CommercialData(TaxType::SALES_TAX, CommercialIndicator::LEVEL_III);
            $commercialData->poNumber = 1784951399984509620;
            $commercialData->taxAmount = 0;
            $commercialData->destinationPostalCode = '85212';
            $commercialData->destinationCountryCode = "USA";
            $commercialData->originPostalCode = "22193";
            $commercialData->summaryCommodityCode = "SCC";
            $commercialData->customerVatNumber = "123456789";
            $commercialData->vatInvoiceNumber = "UVATREF162";
            $datetime = new DateTime();
            $formattedDateTime = str_replace(",", "T", date_format($datetime, 'Y-m-d,H:i:s'));
            $commercialData->orderDate = $formattedDateTime;
            $commercialData->freightAmount = 0.01;
            $commercialData->dutyAmount = 0.01;
            $commercialData->additionalTaxDetails = new AdditionalTaxDetails(
                .01,
                TaxCategory::VAT,
                .04,
                "VAT"
            );
            
            $lineItem1 = new CommercialLineItem;
            $lineItem1->productCode = "PRDCD1";
            $lineItem1->name = "PRDCD1NAME";
            $lineItem1->unitCost = 0.01;
            $lineItem1->quantity = 1;
            $lineItem1->unitOfMeasure = "METER";
            $lineItem1->description = "PRODUCT 1 NOTES";
            $lineItem1->commodityCode = "12DIGIT ACCO";
            $lineItem1->alternateTaxId = "1234567890";
            $lineItem1->creditDebitIndicator = CreditDebitIndicator::CREDIT;
            $lineItem1->discountDetails = new DiscountDetails(
                .50,
                "Indep Sale 1",
                .1,
                "SALE"
            );
    
            $lineItem2 = new CommercialLineItem;
            $lineItem2->productCode = "PRDCD2";
            $lineItem2->name = "PRDCD2NAME";
            $lineItem2->unitCost = 0.01;
            $lineItem2->quantity = 1;
            $lineItem2->unitOfMeasure = "METER";
            $lineItem2->description = "PRODUCT 2 NOTES";
            $lineItem2->commodityCode = "12DIGIT ACCO";
            $lineItem2->alternateTaxId = "1234567890";
            $lineItem2->creditDebitIndicator = CreditDebitIndicator::DEBIT;
            $lineItem2->discountDetails = new DiscountDetails(
                .50,
                "Indep Sale 1",
                .1,
                "SALE"
            );
            
            $commercialData->addLineItems($lineItem1, $lineItem2); // can pass multiple line items or just call this function multiple times

            $cpcEditResponse = $response->edit()
                ->withCommercialData($commercialData)
                ->withTaxType(TaxType::NOT_USED)
                ->execute();

            $this->assertEquals('00', $cpcEditResponse->responseCode);
        } else {
            $this->assertTrue(false);
        }
    }

    // test w/o line items, which seem to be optional
    public function test02aVisaLevel3() {
        $response = $this->getVisaManual()->charge(112.34) // amount results in commercialIndicator val of 'B'
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withCommercialRequest(true)
            ->withAddress($this->getAddy())
            ->execute();

        if ($response->commercialIndicator === 'B') {
            $commercialData = new CommercialData(TaxType::SALES_TAX, CommercialIndicator::LEVEL_III);
            $commercialData->poNumber = 1784951399984509620;
            $commercialData->taxAmount = 0;
            $commercialData->destinationPostalCode = '85212';
            $commercialData->destinationCountryCode = "USA";
            $commercialData->originPostalCode = "22193";
            $commercialData->summaryCommodityCode = "SCC";
            $commercialData->customerVatNumber = "123456789";
            $commercialData->vatInvoiceNumber = "UVATREF162";
            $datetime = new DateTime();
            $formattedDateTime = str_replace(",", "T", date_format($datetime, 'Y-m-d,H:i:s'));
            $commercialData->orderDate = $formattedDateTime;
            $commercialData->freightAmount = 0.01;
            $commercialData->dutyAmount = 0.01;
            $commercialData->additionalTaxDetails = new AdditionalTaxDetails(
                .01,
                TaxCategory::VAT,
                .04,
                "VAT"
            );

            $cpcEditResponse = $response->edit()
                ->withCommercialData($commercialData)
                ->withTaxType(TaxType::NOT_USED)
                ->execute();

            $this->assertEquals('00', $cpcEditResponse->responseCode);
        } else {
            $this->assertTrue(false);
        }
    }

    public function test03MasterCardLevel3() {
        $response = $this->getMasterCardManual()->charge(111.06) // amount results in commercialIndicator val of 'S'
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->withAllowDuplicates(true)
            ->withAddress($this->getAddy())
            ->execute();

        if ($response->commercialIndicator === 'S') {
            $commercialData = new CommercialData(TaxType::SALES_TAX, CommercialIndicator::LEVEL_III);
            $commercialData->poNumber = 1784951399984509620;
            $commercialData->taxAmount = 0;
            
            $lineItem1 = new CommercialLineItem;
            $lineItem1->productCode = "PRDCD1";
            $lineItem1->unitCost = 0.01;
            $lineItem1->quantity = 1;
            $lineItem1->unitOfMeasure = "METER";
            $lineItem1->description = "PRODUCT 1 NOTES";
    
            $lineItem2 = new CommercialLineItem;
            $lineItem2->productCode = "PRDCD2";
            $lineItem2->unitCost = 0.01;
            $lineItem2->quantity = 1;
            $lineItem2->unitOfMeasure = "METER";
            $lineItem2->description = "PRODUCT 2 NOTES";
            
            $commercialData->addLineItems($lineItem1, $lineItem2); // can pass multiple line items or just call this function multiple times

            $cpcEditResponse = $response->edit()
                ->withCommercialData($commercialData)
                ->withTaxType(TaxType::NOT_USED)
                ->execute();

            $this->assertEquals('00', $cpcEditResponse->responseCode);
        } else {
            $this->assertTrue(false);
        }
    }

    protected function getVisaManual() {
        $visaManual = new CreditCardData();
        $visaManual->number = '4012000098765439';
        $visaManual->expMonth = 12;
        $visaManual->expYear = TestCards::validCardExpYear();
        $visaManual->cvn = '999';
        return $visaManual;
    }

    protected function getMasterCardManual() {
        $masterCardManual = new CreditCardData();
        $masterCardManual->number = '5146315000000055';
        $masterCardManual->expMonth = 12;
        $masterCardManual->expYear = TestCards::validCardExpYear();
        $masterCardManual->cvn = '998';
        return $masterCardManual;
    }

    protected function getAddy() {
        $addy = new Address();
        $addy->streetAddress1 = 'address line 1 contents';
        $addy->postalCode = '47130';
        return $addy;
    }
}

