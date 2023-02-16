<?php

namespace Gateways\TransactionApiConnector;

use GlobalPayments\Api\Entities\{Address, Customer, PhoneNumber, Transaction};
use GlobalPayments\Api\Entities\Enums\{
    AddressType,
    EcommerceIndicator,
    PaymentMethodType,
    PaymentMethodUsageMode,
    PhoneNumberType,
    TransactionModifier,
    TransactionLanguage,
    TransactionType
};
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class TransactionApiCreditTest extends TestCase
{
    /**
     * @var CreditCardData $card
     */
    private $card;
    private $address;
    private $addressCa;
    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->cvn = "131";
        $this->card->expMonth = date('m');
        $this->card->expYear = substr(date('Y', strtotime('+1 year')), -2);
        $this->card->cardHolderName = "James Mason";

        $this->address = new Address();
        $this->address->streetAddress1 = "2600 NW";
        $this->address->streetAddress2 = "23th Street";
        $this->address->city = "Lindon";
        $this->address->state = "Utah";
        $this->address->country = "USA";
        $this->address->postalCode = "84042";

        $this->addressCa = new Address();
        $this->addressCa->streetAddress1 = "Suite 101-290";
        $this->addressCa->streetAddress2 = "Suite 109";
        $this->addressCa->city = "Diamond Bar";
        $this->addressCa->state = "CA";
        $this->addressCa->country = "Canada";
        $this->addressCa->postalCode = "91765";

        $this->customer =  new Customer();
        $this->customer->id = "2e39a948-2a9e-4b4a-9c59-0b96765343b7";
        $this->customer->title = "Mr.";
        $this->customer->firstName = "Joe";
        $this->customer->middleName = "Henry";
        $this->customer->lastName = "Doe";
        $this->customer->businessName = "ABC Company LLC";
        $this->customer->email = "joe.doe@gmail.com";
        $this->customer->dateOfBirth = "1980-01-01";
        $this->customer->mobilePhone = new PhoneNumber('+35', '312345678', PhoneNumberType::MOBILE);
        $this->customer->homePhone = new PhoneNumber('+1', '12345899', PhoneNumberType::HOME);
    }

    public function setUpConfig($country = "US")
    {
        $config = new TransactionApiConfig();
        $config->accountCredential = '800000052925:80039923:eWcWNJhfxiJ7QyEHSHndWk4VHKbSmSue';
        $config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $config->apiVersion        = '2021-04-08';
        $config->apiPartnerName    = 'mobile_sdk';
        $config->country           = $country;
        $config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    # credit verify | auth
    public function test001CreditAuthorization()
    {
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test099CreditAuthorizationWithTokenUS()
    {
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test100CreditAuthorizationWithSingleTokenUS()
    {
        $this->markTestSkipped('To run this test you need to generate single use token');

        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        // temporary token
        $this->card = new CreditCardData();
        $this->card->token = "abaac872-4ae0-4f46-ab89-61cdb9ccf11b";

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withRequestMultiUseToken(false)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }


    public function test002CACreditAuthorization()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('124')
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test098CreditAuthorizationWithTokenCA()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test101CreditAuthorizationWithSingleTokenCA()
    {
        $this->markTestSkipped('To run this test you need to generate single use token');

        $transData = $this->getTransactionData('CA');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA')
        );

        // temporary token
        $this->card = new CreditCardData();
        $this->card->token = "0f6c4011-016d-4cce-bca4-87e63bc8187e";

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withRequestMultiUseToken(false)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test003USCreditVerify()
    {
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transaction = $this->card->verify('0.0')
            ->withCurrency('840')
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test110CreditVerifyWithTokenUS()
    {
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transaction = $this->card->verify('0.0')
            ->withCurrency('840')
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test108CACreditVerify()
    {
        $transData = $this->getTransactionData('CA');
        $transData->addressVerificationService = true;
        $transData->generateReceipt = true;

        $transaction = $this->card->verify('0.0')
            ->withCurrency('840')
            ->withAllowPartialAuth(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    public function test112CreditVerifyWithTokenCA()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $transaction = $this->card->verify('0.0')
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
        $this->assertEquals("approved", $transaction->responseCode);
    }

    # end credit verify | auth

    # credit sale

    public function test004WithTokenUSCreditSale()
    {
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = false;
        $transData->generateReceipt = false;

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withAllowPartialAuth(false)
            ->withRequestMultiUseToken(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction->token);

        $transData = $this->getTransactionData('US');
        $this->card->token = $transaction->token;
        $response = $this->card->charge('5.00')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test004USCreditSale()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('23.70')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test200USCreditSaleWithReferenceId()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('8.70')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withClientTransactionId(str_shuffle('abcdefg123212'))
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test005WithTokenCACreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);

        $response = $this->card->charge('19.80')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test005CARegionCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->charge('16.80')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    # end credit sale

    # credit void

    public function test006USVoidCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('15.50')
            ->withCurrency('840')
            ->withInvoiceNumber('123456')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditReturnId = $response->transactionId;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromId($creditReturnId);
        $transaction->originalTransactionType = "REFUND";

        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("15.50")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test006CAVoidCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->charge('8.80')
            ->withCurrency('124')
            ->withInvoiceNumber('1211200')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditReturnId = $response->transactionId;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromId($creditReturnId);
        $transaction->originalTransactionType = "REFUND";

        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("8.80")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test007USReferenceVoidCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('3.00')
            ->withCurrency('840')
            ->withInvoiceNumber(str_shuffle('123456789'))
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $referenceNumber = $response->referenceNumber;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("3.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test008CAReferenceVoidCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->charge('3.00')
            ->withCurrency('124')
            ->withInvoiceNumber('877762652')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $referenceNumber = $response->referenceNumber;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('124')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("3.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test009USVoidCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData(
            'US',
            CountryUtils::getNumericCodeByCountry('US'),
            TransactionLanguage::EN_US
        );

        $response = $this->card->refund('2.00')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditReturnId = $response->transactionId;

        $transaction = Transaction::fromId($creditReturnId, null, PaymentMethodType::CREDIT);
        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::REFUND)
            ->withAmount("2.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test010CAVoidCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->refund('2.00')
            ->withCurrency('124')
            ->withInvoiceNumber('239087')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditReturnId = $response->transactionId;

        $transaction = Transaction::fromId($creditReturnId);
        $response = $transaction->void()
            ->withCurrency('124')
            ->withOriginalTransactionType(TransactionType::REFUND)
            ->withAmount("2.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test011USReferenceVoidCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('6.00')
            ->withCurrency('840')
            ->withInvoiceNumber('1445444')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $referenceNumber = $response->referenceNumber;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::REFUND)
            ->withAmount("6.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test012CAReferenceVoidCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->refund('12.00')
            ->withCurrency('124')
            ->withInvoiceNumber('239087')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $referenceNumber = $response->referenceNumber;

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('124')
            ->withOriginalTransactionType(TransactionType::REFUND)
            ->withAmount("12.00")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    # end credit void

    # credit edit

    public function test012USByCreditSaleId()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('11.50')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditSaleId = $response->transactionId;

        $transData = new TransactionApiData();
        $transData->generateReceipt = true;

        $transaction = Transaction::fromId($creditSaleId);

        $response = $transaction->edit()
            ->withAmount("9.50")
            ->withInvoiceNumber("239087")
            ->withAllowDuplicates(true)
            ->withGratuity("5.00")
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($creditSaleId, $response->transactionId);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test013CAByCreditSaleId()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->charge('15.00')
            ->withCurrency('124')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditSaleId = $response->transactionId;
        $transaction = Transaction::fromId($creditSaleId, null, PaymentMethodType::CREDIT);
        $transData = new TransactionApiData();
        $transData->generateReceipt = true;
        $transaction->transactionId = $creditSaleId;

        $response = $transaction->edit()
            ->withAmount("15.00")
            ->withInvoiceNumber("178988")
            ->withAllowDuplicates(true)
            ->withGratuity("10.00")
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($creditSaleId, $response->transactionId);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test014USByCreditSaleReferenceId()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('17.90')
            ->withCurrency('840')
            ->withInvoiceNumber('1255552')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $creditRefId = $response->referenceNumber;
        $transaction = Transaction::fromClientTransactionId($creditRefId);
        $transData = new TransactionApiData();
        $transData->generateReceipt = true;

        $response = $transaction->edit()
            ->withModifier(TransactionModifier::ADDITIONAL)
            ->withAmount("10.00")
            ->withInvoiceNumber("1255552")
            ->withAllowDuplicates(true)
            ->withGratuity("10.00")
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($creditRefId, $response->referenceNumber);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test015CAByCreditSaleReferenceId()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData('CA');
        $transData->language = TransactionLanguage::EN_CA;

        $response = $this->card->charge('11.00')
            ->withCurrency('124')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);
        $creditSaleId = $response->transactionId;

        $creditRefId = $response->referenceNumber;
        $transaction = Transaction::fromClientTransactionId($creditRefId);
        $transData = new TransactionApiData();
        $transData->generateReceipt = true;

        $response = $transaction->edit()
            ->withModifier(TransactionModifier::ADDITIONAL)
            ->withAmount("9.00")
            ->withInvoiceNumber("178988")
            ->withAllowDuplicates(true)
            ->withGratuity("5.00")
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($creditRefId, $response->referenceNumber);
        $this->assertEquals("approved", $response->responseCode);
    }

    # end credit edit

    # partially approved

    public function test016USPartiallyApprovedCreditSale()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('13.17')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAllowPartialAuth(true)
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("partially_approved", $response->responseCode);
    }

    public function test016USVoidedPartiallyApprovedCreditSale()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('13.17')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAllowPartialAuth(true)
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("partially_approved", $response->responseCode);

        $referenceNumber = $response->referenceNumber;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('840')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("5.32")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    public function test017CAPartiallyApprovedCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->charge('13.17')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAllowPartialAuth(true)
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("partially_approved", $response->responseCode);
    }

    public function test017VoidedCAPartiallyApprovedCreditSale()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->charge('13.17')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAllowPartialAuth(true)
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("partially_approved", $response->responseCode);

        $referenceNumber = $response->referenceNumber;

        ServicesContainer::configureService($this->setUpConfig());

        $transaction = Transaction::fromClientTransactionId($referenceNumber);

        $response = $transaction->void()
            ->withCurrency('124')
            ->withOriginalTransactionType(TransactionType::SALE)
            ->withAmount("5.32")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("voided", $response->responseCode);
    }

    # end partially approved

    private function getTransactionData($region, $countryCode = null, $lang = null)
    {
        $transData = new TransactionApiData();
        $transData->countryCode = isset($countryCode) ? $countryCode : CountryUtils::getNumericCodeByCountry('US');
        $transData->ecommerceIndicator = EcommerceIndicator::ECOMMERCE_INDICATOR_2;
        $transData->language = isset($lang) ? $lang : TransactionLanguage::EN_US;
        $transData->softDescriptor = "soft";
        $transData->region = $region;
        return $transData;
    }
}
