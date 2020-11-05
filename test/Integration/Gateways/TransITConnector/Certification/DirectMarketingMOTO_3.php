<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector\Certification;

use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\Entities\AdditionalTaxDetails;
use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\CommercialLineItem;
use GlobalPayments\Api\Entities\DiscountDetails;
use GlobalPayments\Api\Entities\Enums\CardDataSource;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\CreditDebitIndicator;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\OperatingEnvironment;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\TaxCategory;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\Services\BatchService;
use PHPUnit\Framework\TestCase;

final class DirectMarketingMOTO_3 extends TestCase {
    public function setup() : void {
        ServicesContainer::configureService($this->getConfig());
    }

    public function getConfig() { 
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322601';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
        $config->developerId = '003226G001';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        $config->acceptorConfig = new AcceptorConfig(); // might need to adjust this per transaction or per file
        $config->acceptorConfig->operatingEnvironment = OperatingEnvironment::ON_MERCHANT_PREMISES_ATTENDED;
        $config->acceptorConfig->cardDataSource = CardDataSource::PHONE;
        return $config;
    }

    public function getMailConfig() {
        $mailConfig = $this->getConfig();
        $mailConfig->acceptorConfig->cardDataSource = CardDataSource::MAIL;
        return $mailConfig;
    }

    public static $visaMUT;
    public static $visaMUTCardBrandTransactionId;
    public static $masterCardMUT;
    public static $test22VoidTarget;
    public static $test23VoidTarget;
    public static $test29RefundTarget;
    public static $test30RefundTarget;
    public static $test32VoidTarget;

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
            ->withClientTransactionId('test04_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test05DiscoverSale() {
        $response = $this->getDiscover()->charge(12.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test05_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test06DinersAuth() {
        $response = $this->getDiners()->authorize(6.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test06_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
        // $this->assertEquals(5.55, $response->authorizedAmount); test script is wrong, gateway doesn't return partial auth on this test
    }

    public function test07MasterCardSale() {
        $response = $this->getMCUnclassifiedTIC()->charge(15.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test07_' . time())
            ->execute();

        self::$test22VoidTarget = $response->transactionId;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test08JCBSale() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getJCB()->charge(13.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test08_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test09VisaSale() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getVisa1()->charge(32.49)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test09_' . time())
            ->execute();

        self::$test30RefundTarget = $response->transactionId;
        $this->assertEquals('00', $response->responseCode);
    }

    public function test10DiscoverCUPSale() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getDiscoverCUP()->charge(7.05)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test10_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test11VisaSale() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getVisa1()->charge(11.12)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test11_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
        self::$test29RefundTarget = $response->transactionId;
    }

    public function test12AMEXSale() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getAmex()->charge(4.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test12_' . time())
            ->execute();

        self::$test23VoidTarget = $response->transactionId;

        $this->assertEquals('00', $response->responseCode);
    }

    public function test13VisaVerify() {
        $response = $this->getVisa1()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test13_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test14MasterCardVerify() {
        $response = $this->getMCUnclassifiedTIC()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test14_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test15AMEXVerify() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getAmex()->verify()
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test15_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test16VisaLvl3() {
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

    public function test17MasterCardLvl3() {
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

    public function testGenerateTokensForUseWithNextTests() {
        $response1 = $this->getVisa1()->tokenize()
            ->execute();

        $this->assertEquals('00', $response1->responseCode);
        self::$visaMUT = $response1->token;

        $response2 = $this->getMCUnclassifiedTIC()->tokenize()
            ->execute();
        
        $this->assertEquals('00', $response2->responseCode);
        self::$masterCardMUT = $response2->token;
    }

    public function test18VisaCardAuth() {
        $card = $this->getVisa1();
        $card->number = self::$visaMUT;

        $response = $card->verify()
            ->withAddress($this->getAVSData())
            ->withCardOnFile(true)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
        self::$visaMUTCardBrandTransactionId = $response->cardBrandTransactionId;
    }

    public function test19VisaCIT() {
        $storedcreds = new StoredCredential;
        $storedcreds->initiator = StoredCredentialInitiator::MERCHANT;

        $card = $this->getVisa1();;
        $card->token = self::$visaMUT;
        $card->number = null;

        $response = $card->charge(25.50)
            ->withCurrency('USD')
            ->withStoredCredential($storedcreds)
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test19_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test20MCCIT() {
        $storedcreds = new StoredCredential;
        $storedcreds->initiator = StoredCredentialInitiator::MERCHANT;

        $cardAsMUT = $this->getMCUnclassifiedTIC();
        $cardAsMUT->number = self::$masterCardMUT;

        $response = $cardAsMUT->charge(29.75)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test20_' . time())
            ->withStoredCredential($storedcreds)
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test21VisaMIT() {
        $storedcreds = new StoredCredential;
        $storedcreds->initiator = StoredCredentialInitiator::MERCHANT;
        $storedcreds->cardBrandTransactionId = self::$visaMUTCardBrandTransactionId;

        $card = $this->getVisa1();
        $card->number = self::$visaMUT;

        $response = $card->charge(32.49)
            ->withCurrency('USD')
            ->withStoredCredential($storedcreds)
            ->withAddress($this->getAVSData())
            ->withCardOnFile(true)
            ->withClientTransactionId('test21_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test22PartialVoid() {
        $response = Transaction::fromId(self::$test22VoidTarget)
            ->void(5.00)
            ->withDescription('PARTIAL_REVERSAL')
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test23FullVoid() {
        $response = Transaction::fromId(self::$test23VoidTarget)
            ->void()
            ->withDescription('POST_AUTH_USER_DECLINE')
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test24VisaMultiCapture () {
        $response = $this->getVisa1()->authorize(30.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test24_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $firstCaptureResponse = $response->capture(15.00)
            ->withMultiCapture(1,2)
            ->execute();

        $this->assertEquals('00', $firstCaptureResponse->responseCode);

        $secondCaptureResponse = $response->capture(15.00)
            ->withMultiCapture(2,2)
            ->execute();

        $this->assertEquals('00', $secondCaptureResponse->responseCode);
    }

    public function test25MCMultiCapture () {
        ServicesContainer::configureService($this->getMailConfig());
        
        $response = $this->getMCUnclassifiedTIC()->authorize(50.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test25_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $firstCaptureResponse = $response->capture(30.00)
            ->withMultiCapture(1,3)
            ->execute();

        $this->assertEquals('00', $firstCaptureResponse->responseCode);

        $secondCaptureResponse = $response->capture(10.00)
            ->withMultiCapture(2,3)
            ->execute();

        $this->assertEquals('00', $secondCaptureResponse->responseCode);

        $thirdCaptureResponse = $response->capture(10.00)
            ->withMultiCapture(3,3)
            ->execute();

        $this->assertEquals('00', $thirdCaptureResponse->responseCode);
    }

    public function test26MCMultiCapture () {
        $response = $this->getMCUnclassifiedTIC()->authorize(60.00)
            ->withCurrency('USD')
            ->withAddress($this->getAVSData())
            ->withClientTransactionId('test26_' . time())
            ->execute();

        $this->assertEquals('00', $response->responseCode);

        $captureResponse = $response->capture()
            ->withMultiCapture()
            ->execute();

        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function test27CloseBatch () {
        $response = BatchService::closeBatch();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test28SleepBeforeProceeding () { // test script says, "Once settled wait until after the next :15 minutes to the hour before continuing with below steps", so this function should satisfy that requirement.
        sleep(3900);
    }

    public function test29RefundWithReference () {
        $response = Transaction::fromId(self::$test29RefundTarget)
            ->refund()
            ->withCurrency("USD")
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test30RefundWithReference () {
        $response = Transaction::fromId(self::$test30RefundTarget)
            ->refund()
            ->withCurrency("USD")
            ->execute();

        $this->assertEquals('00', $response->responseCode);
        self::$test32VoidTarget = $response->transactionId;
    }

    public function test32VoidRefund () {
        $response = Transaction::fromId(self::$test32VoidTarget)
            ->void()
            ->execute();

        $this->assertEquals('00', $response->responseCode);
    }

    public function test33CloseBatch () {
        $response = BatchService::closeBatch();

        $this->assertEquals('00', $response->responseCode);
    }

    public function getVisa1 () {
        $card = new CreditCardData;
        $card->number           = 4012000098765439;
        $card->expYear          = 20; // magic number
        $card->expMonth         = 12;
        $card->cvn              = 999;
        $card->cardType = CardType::VISA;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getVisa2 () {
        $card = new CreditCardData;
        $card->number   = 4012881888818888;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 999;
        $card->cardType = CardType::VISA;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCUnclassifiedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146315000000055;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCSwipeTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312200000035;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCKeyedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312620000045;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMC2BIN () {
        $card = new CreditCardData;
        $card->number   = 2223000048400011;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getAmex () {
        $card = new CreditCardData;
        $card->number   = 371449635392376;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 9997;
        $card->cardType = CardType::AMEX;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscover () {
        $card = new CreditCardData;
        $card->number   = 6011000993026909;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscoverCUP () {
        $card = new CreditCardData;
        $card->number   = 6282000123842342;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscoverCUP2 () {
        $card = new CreditCardData;
        $card->number   = 6221261111112650;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiners () {
        $card = new CreditCardData;
        $card->number   = 3055155515160018;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DINERS;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getJCB () {
        $card = new CreditCardData;
        $card->number   = 3530142019945859;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::JCB;
        $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getAVSData () {
        $address = new Address();
        $address->streetAddress1    = '8320';
        $address->postalCode        = '85284';
        return $address;
    }
}
