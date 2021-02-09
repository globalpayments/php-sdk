<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector\Certification;

use GlobalPayments\Api\Entities\AdditionalTaxDetails;
use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\CommercialLineItem;
use GlobalPayments\Api\Entities\DiscountDetails;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\CreditDebitIndicator;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\TaxCategory;
use GlobalPayments\Api\Entities\Enums\UcafIndicator;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\Services\BatchService;
use PHPUnit\Framework\TestCase;

final class Ecommerce_3 extends TestCase
{
    public function setup() : void
    {
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig() { 
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'Hrcb^619';
        $config->deviceId = '88700000322601';
        $config->transactionKey = '57ZL83P6A2V8KGI49QWK017C7WXG03O8';
        $config->developerId = '003226G001';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        $config->acceptorConfig = new AcceptorConfig(); // might need to adjust this per transaction or per file
        return $config;
    }

    public static $test20VoidTarget;
    public static $test21VoidTarget;
    public static $test30MUT;
    public static $test31MUT;

    // These are all stolen from dotnet cert file
    public function test01VisaLevelII() {
        $commercialData = new CommercialData(TaxType::NOT_USED);
        $commercialData->poNumber = '9876543210';
        $commercialData->taxAmount = 0;

        $response = $this->getVisa1()->charge(.52)
            ->withCurrency("USD")
            ->withCommercialData($commercialData)
            ->withDescription("test01VisaLevelII")
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test02MasterCardLevelII() {
        $commercialData = new CommercialData(TaxType::SALES_TAX);
        $commercialData->poNumber = '9876543210';
        $commercialData->taxAmount = .02;

        $response = $this->getMCKeyedTIC()->charge(.52)
            ->withCurrency("USD")
            ->withCommercialData($commercialData)
            ->withDescription("test02MasterCardLevelII")
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

        $response = $this->getAmex()->charge(1.50)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->withDescription('test03AmexManualLevelII')
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test04MasterCard2BINSale() {
        $response = $this->getMC2BIN()->charge(11.10)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test04' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test05DiscoverSale() {
        $response = $this->getDiscover()->charge(12.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test05' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test06DinersAuth() {
        $response = $this->getDiners()->authorize(6.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test06' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
        // $this->assertEquals(5.55, $response->authorizedAmount); test script is wrong, gateway doesn't return partial auth on this test
    }

    public function test07MasterCardSale() {
        $response = $this->getMCUnclassifiedTIC()->charge(15.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test07' . time())
            ->execute();

        self::$test20VoidTarget = $response->transactionId;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test08MasterCardSale() {
        $response = $this->getMCUnclassifiedTIC()->charge(34.13)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test08' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test09JCBSale() {
        $response = $this->getJCB()->charge(13.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test09' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test10AMEX() {
        $card = $this->getAmex();
        $card->cvn = null;

        $response = $card->charge(13.50)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test10' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test11VisaSale() {
        $response = $this->getVisa1()->charge(32.49)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test11' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test12DiscoverCUPSale() {
        $response = $this->getDiscoverCUP()->charge(10.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test12' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test13VisaSale() {
        $response = $this->getVisa1()->charge(11.12)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test13' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test14AMEXSale() {
        $response = $this->getAmex()->charge(4.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test14' . time())
            ->execute();

        self::$test21VoidTarget = $response->transactionId;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test15VisaVerify() {
        $response = $this->getVisa1()->verify()
            ->withRequestMultiUseToken(true)
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test15' . time())
            ->execute();

        self::$test30MUT = $response->token;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test15aVisaVerify() {
        $response = $this->getVisa1()->verify()
            // ->withRequestMultiUseToken(true)
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test15a' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test16MasterCardVerify() {
        $response = $this->getMCUnclassifiedTIC()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test16' . time())
            ->withRequestMultiUseToken(true)
            ->execute();

        self::$test31MUT = $response->token;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test16aMasterCardVerify() {
        $response = $this->getMCUnclassifiedTIC()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test16a' . time())
            // ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test17AMEXVerify() {
        $response = $this->getAmex()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test17' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test18VisaLvl3() {
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
        $lineItem1->taxAmount = 0;
        $lineItem1->taxName = 'item 1 name';

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
        $lineItem2->taxAmount = .03;
        $lineItem2->taxName = 'a tax name here';
        $lineItem2->taxType = TaxType::SALES_TAX;
        $lineItem2->taxPercentage = .69;
        
        $commercialData->addLineItems($lineItem1, $lineItem2); // can pass multiple line items or just call this function multiple times

        $response = $this->getVisa1()->charge(.53)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test19MasterCardLvl3() {
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
        $lineItem->discountDetails = new DiscountDetails(
            .01, 
            'little discount',
            1,
            'discount type 1'
        );
        $lineItem->taxAmount = 1;
        $lineItem->taxName = 'a tax name here';
        $lineItem->taxPercentage = 12;

        $commercialData->addLineItems($lineItem);

        $response = $this->getMCUnclassifiedTIC()->charge(.53)
            ->withCurrency('USD')
            ->withCommercialData($commercialData)
            ->withAddress($this->getAVSData())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test20PartialVoid() {
        $response = Transaction::fromId(self::$test20VoidTarget)
            ->void(5.00)
            ->withDescription('PARTIAL_REVERSAL')
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test21FullVoid() {
        $response = Transaction::fromId(self::$test21VoidTarget)
            ->void()
            ->withDescription('POST_AUTH_USER_DECLINE')
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test22Visa3DS () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->secureCode = '1234567890123456789012345678901234567890';
        // $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        // $threeDeeInfo->setVersion(Secure3dVersion::ONE);

        $card = $this->getVisa1();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(1.01)
            ->withCurrency('USD')
            ->withClientTransactionId('test22_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test23MC3DS_V1 () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->secureCode = '12345678901234567890123456789012';
        $threeDeeInfo->authenticationType = '21';
        $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        $threeDeeInfo->setVersion(Secure3dVersion::ONE);

        $card = $this->getMCUnclassifiedTIC();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(34.02)
            ->withCurrency('USD')
            ->withClientTransactionId('test23_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test24MC3DS_V2 () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->secureCode = '12345678901234567890123456789012';
        $threeDeeInfo->authenticationType = '21';
        $threeDeeInfo->directoryServerTransactionId = 'c272b04f-6e7b-43a2-bb78-90f4fb94aa25';
        $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        $threeDeeInfo->setVersion(Secure3dVersion::TWO);

        $card = $this->getMCUnclassifiedTIC();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(34.06)
            ->withCurrency('USD')
            ->withClientTransactionId('test24_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test25Discover3DS () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->secureCode = '1234567890123456789012345678901234567890';
        // $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        // $threeDeeInfo->setVersion(Secure3dVersion::ONE);

        $card = $this->getDiscover();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(45.02)
            ->withCurrency('USD')
            ->withClientTransactionId('test25_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test26Amex3DS () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->secureCode = '1234567890123456789012345678901234567890';
        // $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        // $threeDeeInfo->setVersion(Secure3dVersion::ONE);

        $card = $this->getAmex();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(32.02)
            ->withCurrency('USD')
            ->withClientTransactionId('test26_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test27MC3DS_V2_2 () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 5;
        $threeDeeInfo->authenticationValue = 'ODQzNjgwNjU0ZjM3N2JmYTg0NTM=';
        $threeDeeInfo->authenticationType = '24';
        $threeDeeInfo->directoryServerTransactionId = 'c272b04f-6e7b-43a2-bb78-90f4fb94aa25';
        $threeDeeInfo->ucafIndicator = UcafIndicator::MERCHANT_RISK_BASED;
        $threeDeeInfo->setVersion(Secure3dVersion::TWO);

        $card = $this->getMCUnclassifiedTIC();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(34.05)
            ->withCurrency('USD')
            ->withClientTransactionId('test27_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test28Visa3DS_ECI6 () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 6;
        $threeDeeInfo->secureCode = '1234567890123456789012345678901234567890';
        // $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        // $threeDeeInfo->setVersion(Secure3dVersion::TWO);

        $card = $this->getVisa1();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(.81)
            ->withCurrency('USD')
            ->withClientTransactionId('test28_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test29MC3DS_ECI6 () {
        $threeDeeInfo = new ThreeDSecure();
        $threeDeeInfo->eci = 6;
        $threeDeeInfo->secureCode = '12345678901234567890123456789012';
        $threeDeeInfo->ucafIndicator = UcafIndicator::FULLY_AUTHENTICATED;
        $threeDeeInfo->setVersion(Secure3dVersion::ONE);

        $card = $this->getMCUnclassifiedTIC();
        $card->threeDSecure = $threeDeeInfo;

        $response = $card->charge(29.00)
            ->withCurrency('USD')
            ->withClientTransactionId('test29_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test30SaleVisaCIT () {
        $storedcreds = new StoredCredential;
        $storedcreds->initiator = StoredCredentialInitiator::MERCHANT;

        $cardAsMUT = $this->getVisa1();
        $cardAsMUT->number = self::$test30MUT;

        $response = $cardAsMUT->charge(14.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test30_' . time())
            ->withStoredCredential($storedcreds)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test31SaleNonVisaCIT () {
        $storedcreds = new StoredCredential;
        $storedcreds->initiator = StoredCredentialInitiator::MERCHANT;

        $cardAsMUT = $this->getMCUnclassifiedTIC();
        $cardAsMUT->number = self::$test31MUT;

        $response = $cardAsMUT->charge(15.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test31_' . time())
            ->withStoredCredential($storedcreds)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test32VisaMultiCapture () {
        $response = $this->getVisa1()->authorize(30.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test32_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $firstCaptureResponse = $response->capture(15.00)
            ->withMultiCapture(1, 2)
            ->execute();

        $this->assertEquals('00', $firstCaptureResponse->responseCode);

        $secondCaptureResponse = $response->capture(15.00)
            ->withMultiCapture(2, 2)
            ->execute();

        $this->assertEquals('00', $secondCaptureResponse->responseCode);
    }

    public function test33MCMultiCapture () {
        $response = $this->getMCUnclassifiedTIC()->authorize(50.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test33_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $firstCaptureResponse = $response->capture(30.00)
            ->withMultiCapture(1, 3)
            ->execute();

        $this->assertEquals('00', $firstCaptureResponse->responseCode);

        $secondCaptureResponse = $response->capture(10.00)
            ->withMultiCapture(2, 3)
            ->execute();

        $this->assertEquals('00', $secondCaptureResponse->responseCode);

        $thirdCaptureResponse = $response->capture(10.00)
            ->withMultiCapture(3, 3)
            ->execute();

        $this->assertEquals('00', $thirdCaptureResponse->responseCode);
    }

    public function test34MCMultiCapture () {
        $response = $this->getMCUnclassifiedTIC()->authorize(60.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test34_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $captureResponse = $response->capture()
            ->withMultiCapture()
            ->execute();

        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function test35CloseBatch () {
        $response = BatchService::closeBatch();

        $this->assertEquals('00', $response->responseCode);
    }

    public function getVisa1 () {
        $card = new CreditCardData;
        $card->number   = 4012000098765439;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 999;
        $card->cardType = CardType::VISA;
        return $card;
    }

    public function getVisa2 () {
        $card = new CreditCardData;
        $card->number   = 4012881888818888;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 999;
        $card->cardType = CardType::VISA;
        return $card;
    }

    public function getMCUnclassifiedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146315000000055;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        return $card;
    }

    public function getMCSwipeTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312200000035;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        return $card;
    }

    public function getMCKeyedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312620000045;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        return $card;
    }

    public function getMC2BIN () {
        $card = new CreditCardData;
        $card->number   = 2223000048400011;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        return $card;
    }

    public function getAmex () {
        $card = new CreditCardData;
        $card->number   = 371449635392376;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 9997;
        $card->cardType = CardType::AMEX;
        return $card;
    }

    public function getDiscover () {
        $card = new CreditCardData;
        $card->number   = 6011000993026909;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        return $card;
    }

    public function getDiscoverCUP () {
        $card = new CreditCardData;
        $card->number   = 6282000123842342;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        return $card;
    }

    public function getDiscoverCUP2 () {
        $card = new CreditCardData;
        $card->number   = 6221261111112650;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        return $card;
    }

    public function getDiners () {
        $card = new CreditCardData;
        $card->number   = 3055155515160018;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DINERS;
        return $card;
    }

    public function getJCB () {
        $card = new CreditCardData;
        $card->number   = 3530142019945859;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::JCB;
        return $card;
    }

    public function getAVSData () {
        $address = new Address();
        $address->streetAddress1    = '8320';
        $address->postalCode        = '85284';
        return $address;
    }
}
