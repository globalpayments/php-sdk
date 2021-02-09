<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector;

use GlobalPayments\Api\Entities\AdditionalTaxDetails;
use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\CommercialLineItem;
use GlobalPayments\Api\Entities\DiscountDetails;
use GlobalPayments\Api\Entities\Enums\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\CreditDebitIndicator;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\TaxCategory;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

final class CommercialCardTest extends TestCase {
    public function setup() : void {
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig() {
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322602';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
        $config->developerId = '003226G001';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        $config->acceptorConfig = new AcceptorConfig();
        return $config;
    }

    protected function getAddy() {
        $addy = new Address();
        $addy->streetAddress1 = 'address line 1 contents';
        $addy->postalCode = '47130';
        return $addy;
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

    protected function getAmexManual() {
        $amexManual = new CreditCardData();
        $amexManual->number = "371449635392376";
        $amexManual->expMonth = 12;
        $amexManual->expYear = TestCards::validCardExpYear();
        $amexManual->cvn = "9997";
        return $amexManual;
    }

    // These are all stolen from dotnet cert file
    public function test01VisaManualLevelII() {
        $commercialData = new CommercialData(TaxType::NOT_USED);
        $commercialData->poNumber = '9876543210';
        $commercialData->taxAmount = 0;

        $response = $this->getVisaManual()->charge(52)
            ->withCurrency("USD")
            ->withCommercialData($commercialData)
            ->withAddress($this->getAddy())
            ->withDescription("Test_001_Visa_Level_II_Sale")
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test02MasterCardManualLevelII() {
        $commercialData = new CommercialData(TaxType::SALES_TAX);
        $commercialData->poNumber = '9876543210';
        $commercialData->taxAmount = .02;

        $response = $this->getMasterCardManual()->charge(.52)
            ->withCurrency("USD")
            ->withCommercialData($commercialData)
            ->withAddress($this->getAddy())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test03AmexManualLevelII() {
        $commercialData = new CommercialData(TaxType::NOT_USED);
        $commercialData->supplierReferenceNumber = "123456";
        $commercialData->customerReferenceId = "987654";
        $commercialData->destinationPostalCode = "85284";
        $commercialData->description = "AMEX LEVEL 2 TEST CASE";
        $commercialData->taxAmount = 0;

        $response = $this->getAmexManual()->charge(1.50)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->withAddress($this->getAddy())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test04VisaManualLevelIII() {
        $commercialData = new CommercialData(TaxType::NOT_USED, CommercialIndicator::LEVEL_III);
        $commercialData->poNumber = 1784951399984509620;
        $commercialData->taxAmount = .01;
        $commercialData->destinationPostalCode = '85212';
        $commercialData->destinationCountryCode = "USA";
        $commercialData->originPostalCode = "22193";
        $commercialData->summaryCommodityCode = "SCC";
        $commercialData->customerVatNumber = "123456789";
        $commercialData->vatInvoiceNumber = "UVATREF162";
        $commercialData->orderDate = date('m/d/Y');
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

        $response = $this->getVisaManual()->charge(.53)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->withAddress($this->getAddy())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test05MasterCardManualLevelIII() {
        $commercialData = new CommercialData(TaxType::NOT_USED, CommercialIndicator::LEVEL_III);
        $commercialData->poNumber = "9876543210";
        $commercialData->taxAmount = 0.01;
        $commercialData->destinationPostalCode = "85212";
        $commercialData->destinationCountryCode = "USA";
        $commercialData->originPostalCode = "22193";
        $commercialData->summaryCommodityCode = "SCC";
        $commercialData->customerVatNumber = "123456789";
        $commercialData->vatInvoiceNumber = "UVATREF162";
        $commercialData->orderDate = date('m/d/Y');
        $commercialData->freightAmount = 0.01;
        $commercialData->dutyAmount = 0.01;
        $commercialData->additionalTaxDetails = new AdditionalTaxDetails(.01, TaxCategory::VAT, .04, "VAT");

        $lineItem = new CommercialLineItem;
        $lineItem->productCode = "PRDCD1";
        $lineItem->name = "PRDCD1NAME";
        $lineItem->unitCost = 0.01;
        $lineItem->quantity = 1;
        $lineItem->unitOfMeasure = "METER";
        $lineItem->description = "PRODUCT 1 NOTES";
        $lineItem->commodityCode = "12DIGIT ACCO";
        $lineItem->alternateTaxId = "1234567890";
        $lineItem->creditDebitIndicator = CreditDebitIndicator::CREDIT;
        $commercialData->addLineItems($lineItem);

        $response = $this->getMasterCardManual()->charge(.53)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->withAddress($this->getAddy())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }
}
