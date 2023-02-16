<?php

use GlobalPayments\Api\Entities\{Address, Customer, PhoneNumber, Transaction};
use GlobalPayments\Api\Entities\Enums\{
    AddressType,
    EcommerceIndicator,
    PaymentMethodType,
    PhoneNumberType,
    TransactionLanguage,
    PaymentType
};
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiData;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\PaymentMethods\{ECheck, CreditCardData};
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class TransactionApiCAReportingTest extends TestCase
{
    private $eCheck;

    private $customer;

    private $config;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->cvn = "131";
        $this->card->expMonth = date('m');
        $this->card->expYear = substr(date('Y', strtotime('+1 year')), -2);
        $this->card->cardHolderName = "James Mason";

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


        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '12121';
        $this->eCheck->accountType = "Checking";
        $this->eCheck->checkHolderName = 'Jane Doe';

        $this->customer =  new Customer();
        $this->customer->title = "Mr.";
        $this->customer->firstName = "Joe";
        $this->customer->middle_name = "Henry";
        $this->customer->lastName = "Doe";
        $this->eCheck->customer = $this->customer;
    }
    public function setUpConfigACH()
    {
        $this->config = new TransactionApiConfig();
        $this->config->accountCredential = '800000052925:80039996:58xcGM3pbTtzcidVPY65XBqbB1EzWoD3';
        $this->config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $this->config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $this->config->apiVersion        = '2021-04-08';
        $this->config->apiPartnerName    = 'mobile_sdk';
        $this->config->country           = 'CA';
        $this->config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $this->config;
    }

    public function setUpConfig()
    {
        $this->config = new TransactionApiConfig();
        $this->config->accountCredential = '800000052925:80039923:eWcWNJhfxiJ7QyEHSHndWk4VHKbSmSue';
        $this->config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $this->config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $this->config->apiVersion        = '2021-04-08';
        $this->config->apiPartnerName    = 'mobile_sdk';
        $this->config->country           = 'CA';
        $this->config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $this->config;
    }

    public function test001CreditSaleByCreditSaleId()
    {
        $transData = $this->getTransactionData('CA');

        $response = $this->card->charge('12.80')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withAddress($this->addressCa, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditSaleId = $response->transactionId;

        $response = ReportingService::transactionDetail($creditSaleId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->transactionStatus);
        $this->assertEquals($creditSaleId, $response->transactionId);
    }

    public function test002CreditSaleByReferenceId()
    {
        $transData = $this->getTransactionData('CA');

        $response = $this->card->charge('15.80')
            ->withCurrency('124')
            ->withInvoiceNumber('167892')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withAddress($this->addressCa, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $referenceID = $response->referenceNumber;

        $response = ReportingService::findTransactions()
            ->where(SearchCriteria::CLIENT_TRANSACTION_ID, $referenceID)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->transactionStatus);
        $this->assertEquals($referenceID, $response->referenceNumber);
    }

    public function test003CreditReturnByCreditSaleId()
    {
        ServicesContainer::configureService($this->setUpConfig());

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->refund('2.00')
            ->withCurrency('124')
            ->withInvoiceNumber('123456')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response->transactionId);

        $creditReturnId = $response->transactionId;

        $response = ReportingService::findTransactions($creditReturnId)
            ->where(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::CREDIT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->transactionStatus);
        $this->assertEquals($creditReturnId, $response->transactionId);
    }

    public function test004CreditReturnByCreditReferenceId()
    {
        ServicesContainer::configureService($this->setUpConfig());

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->refund('6.00')
            ->withCurrency('124')
            ->withInvoiceNumber('123456')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response->referenceNumber);
        $creditReferenceId = $response->referenceNumber;

        $response = ReportingService::findTransactions()
            ->where(SearchCriteria::CLIENT_TRANSACTION_ID, $creditReferenceId)
            ->andWith(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::CREDIT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->transactionStatus);
        $this->assertEquals($creditReferenceId, $response->referenceNumber);
    }

    public function test005CheckSaleById()
    {
        ServicesContainer::configureService($this->setUpConfigACH());
        ServicesContainer::configureService($this->setUpConfigACH());

        $this->transData = $this->getTransactionAchData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response->transactionReference->checkSaleId);

        $checkSaleId = $response->transactionReference->checkSaleId;

        $response = ReportingService::findTransactions($checkSaleId)
            ->where(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::ACH)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("check_submitted", $response->transactionStatus);
        $this->assertEquals($checkSaleId, $response->transactionId);
    }

    public function test006CheckSaleByRefId()
    {
        ServicesContainer::configureService($this->setUpConfigACH());

        $this->transData = $this->getTransactionAchData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $checkRefId = $response->referenceNumber;

        $response = ReportingService::findTransactions()
            ->where(SearchCriteria::CLIENT_TRANSACTION_ID, $checkRefId)
            ->andWith(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::ACH)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("check_submitted", $response->transactionStatus);
        $this->assertEquals($checkRefId, $response->referenceNumber);
    }

    public function test007CheckRefundById()
    {
        ServicesContainer::configureService($this->setUpConfigACH());

        $this->transData = $this->getTransactionAchData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->refund(11)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response->transactionReference->checkRefundId);

        $checkRefundId = $response->transactionReference->checkRefundId;

        $response = ReportingService::findTransactions($checkRefundId)
            ->where(SearchCriteria::PAYMENT_TYPE, PaymentType::REFUND)
            ->andWith(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::ACH)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("check_submitted", $response->transactionStatus);
        $this->assertEquals($checkRefundId, $response->transactionId);
    }

    public function test008CheckRefundByRefId()
    {
        ServicesContainer::configureService($this->setUpConfigACH());

        $this->transData = $this->getTransactionAchData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $this->transData->paymentPurposeCode = "150";
        $this->transData->entryClass = "PPD";

        $response = $this->eCheck->refund(18)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $checkRefId = $response->referenceNumber;

        $response = ReportingService::findTransactions()
            ->where(SearchCriteria::CLIENT_TRANSACTION_ID, $checkRefId)
            ->andWith(SearchCriteria::PAYMENT_TYPE, PaymentType::REFUND)
            ->andWith(SearchCriteria::PAYMENT_METHOD_TYPE, PaymentMethodType::ACH)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("check_submitted", $response->transactionStatus);
        $this->assertEquals($checkRefId, $response->referenceNumber);
    }

    private function getTransactionData($region, $countryCode = null, $lang = null)
    {
        $transData = new TransactionApiData();
        $transData->countryCode = isset($countryCode) ? $countryCode : CountryUtils::getNumericCodeByCountry('CA');
        $transData->ecommerceIndicator = EcommerceIndicator::ECOMMERCE_INDICATOR_2;
        $transData->language = isset($lang) ? $lang : TransactionLanguage::EN_CA;
        $transData->softDescriptor = "soft";
        $transData->region = $region;
        return $transData;
    }

    private function getTransactionAchData($region, $language, $countryCode)
    {
        $this->transData = new TransactionApiData();
        $this->transData->countryCode = $countryCode;
        $this->transData->language = $language;
        $this->transData->region = $region;
        $this->transData->checkVerify = false;

        return $this->transData;
    }
}
