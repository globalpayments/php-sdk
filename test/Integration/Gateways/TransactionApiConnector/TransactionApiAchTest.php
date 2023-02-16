<?php

use GlobalPayments\Api\Entities\{Customer, Transaction};
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    TransactionLanguage
};

use GlobalPayments\Api\Entities\TransactionApi\TransactionApiData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class TransactionApiAchTest extends TestCase
{
    private $eCheck;

    private $customer;

    public function setup(): void
    {
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

    public function setUpConfigCA()
    {
        $config = new TransactionApiConfig();
        $config->accountCredential = '800000052925:80039996:58xcGM3pbTtzcidVPY65XBqbB1EzWoD3';
        $config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $config->apiVersion        = '2021-04-08';
        $config->apiPartnerName    = 'mobile_sdk';
        $config->country           = 'CA';
        $config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function setUpConfigUS()
    {
        $config = new TransactionApiConfig();
        $config->accountCredential = '800000052925:80039990:n7j9rGFUml1Du7rcRs7XGYdJdVMmZKzh';
        $config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv';
        $config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs';
        $config->apiVersion        = '2021-04-08';
        $config->apiPartnerName    = 'php_mobile_sdk';
        $config->country           = 'US';
        $config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function testGetNumericCodeExact()
    {
        $result = CountryUtils::getNumericCodeByCountry('US');

        $this->assertNotNull($result);
        $this->assertEquals('840', $result);
    }

    public function test001USCheckSale()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test002USCheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->refund(11)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test003USByCheckRefundId()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withEntryClass("PPD")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $checkSaleId = $response->checkSaleId;
        $transaction = Transaction::fromId($checkSaleId, null, PaymentMethodType::ACH);

        $this->eCheck->account_type = "Checking";

        $response = $transaction->refund(5)
            ->withCurrency('840')
            ->withBankTransferData($this->eCheck)
            ->withEntryClass("PPD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test004USByReferenceIdCheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $referenceID = $response->referenceNumber;
        $transaction = Transaction::fromClientTransactionId($referenceID, null, PaymentMethodType::ACH);

        $response = $transaction->refund(5)
            ->withCurrency('840')
            ->withBankTransferData($this->eCheck)
            ->withEntryClass("PPD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
        $this->assertEquals($referenceID, $response->referenceNumber);
    }

    public function test005CACheckSale()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withTransactionData($this->transData)
            ->withPaymentPurposeCode("150")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test006CACheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->refund(11)
            ->withCurrency('124')
            ->withTransactionData($this->transData)
            ->withPaymentPurposeCode("150")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test007CAByCheckRefundId()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(15)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $checkSaleId = $response->transactionReference->checkSaleId;

        $transaction = Transaction::fromId($checkSaleId, null, PaymentMethodType::ACH);
        $this->eCheck->checkNumber = (string)rand();

        $response = $transaction->refund(6)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withBankTransferData($this->eCheck)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test008CAByReferenceIdCheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(15)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        ServicesContainer::configureService($this->setUpConfigCA());
        $referenceID = $response->referenceNumber;

        $transaction = Transaction::fromClientTransactionId($referenceID, null, PaymentMethodType::ACH);

        $this->eCheck->checkNumber = (string)rand();

        $response = $transaction->refund(8)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withBankTransferData($this->eCheck)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
        $this->assertEquals($referenceID, $response->referenceNumber);
    }

    # with token
    public function test009WithTokenUSCheckSale()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withRequestMultiUseToken(true)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($response->token);

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->token = $response->token;

        $response = $this->eCheck->charge(11)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test010WithTokenCACheckSale()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($response->token);

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->token = $response->token;

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withTransactionData($this->transData)
            ->withPaymentPurposeCode("150")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test011USWithTokenCheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigUS());

        $this->transData = $this->getTransactionData(
            'US',
            TransactionLanguage::EN_US,
            CountryUtils::getNumericCodeByCountry('US')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->routingNumber = '112000066';

        $response = $this->eCheck->charge(15)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withRequestMultiUseToken(true)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($response->token);

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->token = $response->token;

        $response = $this->eCheck->refund(15)
            ->withCurrency('840')
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    public function test012CAWithTokenCheckRefund()
    {
        ServicesContainer::configureService($this->setUpConfigCA());

        $this->transData = $this->getTransactionData(
            'CA',
            TransactionLanguage::EN_CA,
            CountryUtils::getNumericCodeByCountry('CA')
        );

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->branchTransitNumber = "12345";
        $this->eCheck->financialInstitutionNumber = "999";

        $response = $this->eCheck->charge(11)
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($response->token);

        $this->eCheck->checkNumber = (string)rand();
        $this->eCheck->token = $response->token;

        $response = $this->eCheck->refund(15)
            ->withCurrency('124')
            ->withPaymentPurposeCode("150")
            ->withTransactionData($this->transData)
            ->withEntryClass("PPD")
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('check_submitted', $response->responseCode);
    }

    # end with token

    private function getTransactionData($region, $language, $countryCode)
    {
        $this->transData = new TransactionApiData();
        $this->transData->countryCode = $countryCode;
        $this->transData->language = $language;
        $this->transData->region = $region;
        $this->transData->checkVerify = false;

        return $this->transData;
    }
}
