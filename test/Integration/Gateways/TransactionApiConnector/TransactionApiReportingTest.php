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
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiData;
use GlobalPayments\Api\PaymentMethods\{ECheck, CreditCardData};
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class TransactionApiReportingTest extends TestCase
{
    private $eCheck;

    private $customer;

    public $config;

    private $currency = '840';

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '12121';
        $this->eCheck->accountType = "Checking";
        $this->eCheck->checkHolderName = 'Jane Doe';

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

    public function setUpConfig()
    {
        $this->config = new TransactionApiConfig();
        $this->config->accountCredential = '800000052925:80039923:eWcWNJhfxiJ7QyEHSHndWk4VHKbSmSue';
        $this->config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $this->config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $this->config->apiVersion        = '2021-04-08';
        $this->config->apiPartnerName    = 'mobile_sdk';
        $this->config->country           = 'US';
        $this->config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $this->config;
    }

    public function test001CreditSaleByCreditSaleId()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('31.70')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
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
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('32.70')
            ->withCurrency('840')
            ->withInvoiceNumber('178988')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($response->referenceNumber);

        $referenceID = $response->referenceNumber;

        $response = ReportingService::findTransactions()
            ->where(SearchCriteria::CLIENT_TRANSACTION_ID, $referenceID)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->transactionStatus);
        $this->assertEquals($referenceID, $response->referenceNumber);
    }

    public function test003CreditReturnByCreditReturnId()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->refund('33.70')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
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
        $transData = $this->getTransactionData('US');

        $response = $this->card->refund('34.70')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
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
        ServicesContainer::configureService($this->setUpConfig());
        $this->config->accountCredential = "800000052925:80039990:n7j9rGFUml1Du7rcRs7XGYdJdVMmZKzh";

        $this->transData = $this->getTransactionDataAch(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $this->customer->id = null;
        $this->customer->email = null;
        $this->customer->mobilePhone = null;

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withEntryClass("PPD")
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
        ServicesContainer::configureService($this->setUpConfig());
        $this->config->accountCredential = "800000052925:80039990:n7j9rGFUml1Du7rcRs7XGYdJdVMmZKzh";

        $this->transData = $this->getTransactionDataAch(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $this->customer->id = null;
        $this->customer->email = null;
        $this->customer->mobilePhone = null;

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withEntryClass("PPD")
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
        ServicesContainer::configureService($this->setUpConfig());
        $this->config->accountCredential = "800000052925:80039990:n7j9rGFUml1Du7rcRs7XGYdJdVMmZKzh";

        $this->transData = $this->getTransactionDataAch(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $this->customer->id = null;
        $this->customer->email = null;
        $this->customer->mobilePhone = null;

        $response = $this->eCheck->refund(13)
            ->withCurrency('840')
            ->withEntryClass("PPD")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $checkRefundId = $response->checkRefundId;
        
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
        ServicesContainer::configureService($this->setUpConfig());
        $this->config->accountCredential = "800000052925:80039990:n7j9rGFUml1Du7rcRs7XGYdJdVMmZKzh";

        $this->transData = $this->getTransactionDataAch(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $this->customer->id = null;
        $this->customer->email = null;
        $this->customer->mobilePhone = null;

        $response = $this->eCheck->refund(15)
            ->withCurrency('840')
            ->withEntryClass("PPD")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

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
        $transData->countryCode = isset($countryCode) ? $countryCode : CountryUtils::getNumericCodeByCountry('US');
        $transData->ecommerceIndicator = EcommerceIndicator::ECOMMERCE_INDICATOR_2;
        $transData->language = isset($lang) ? $lang : TransactionLanguage::EN_US;
        $transData->softDescriptor = "soft";
        $transData->region = $region;
        return $transData;
    }

    private function getTransactionDataAch($region, $language, $countryCode)
    {
        $this->transData = new TransactionApiData();
        $this->transData->countryCode = $countryCode;
        $this->transData->language = $language;
        $this->transData->region = $region;
        $this->transData->checkVerify = false;

        return $this->transData;
    }
}
