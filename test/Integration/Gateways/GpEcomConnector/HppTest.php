<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\BankPaymentStatus;
use GlobalPayments\Api\Entities\Enums\BlockCardType;
use GlobalPayments\Api\Entities\Enums\ChallengeRequestIndicator;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\HostedPaymentMethods;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\FraudRuleCollection;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector\Hpp\GpEcomHppClient;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class HppTest extends TestCase
{
    private Address $billingAddress;

    private Address $shippingAddress;
    private string $currency = 'EUR';

    private array $hppVersionList = [
        HppVersion::VERSION_1,
        HppVersion::VERSION_2,
        ''
    ];

    protected HostedService $service;
    protected GpEcomHppClient $client;

    protected function config(): GpEcomConfig
    {
        // billing address
        $this->billingAddress = new Address();
        $this->billingAddress->streetAddress1 = 'Flat 123';
        $this->billingAddress->streetAddress2 = 'House 456';
        $this->billingAddress->postalCode = "50001";
        $this->billingAddress->country = "US";

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Flat 456';
        $this->shippingAddress->streetAddress2 = 'House 123';
        $this->shippingAddress->postalCode = "WB3 A21";
        $this->shippingAddress->country = "GB";

        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->refundPassword = "refund";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://requestb.in/10q2bjb1";

        return $config;
    }

    /**
     * @throws ApiException
     */
    public function setup(): void
    {
        $this->service = new HostedService($this->config());
    }

    public function testCreditAuth()
    {
        $config = $this->config();
        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->streetAddress1 = "264 Fowler Avenue";
        $address->streetAddress2 = "Lake Charles";
        $address->city = "Gainesville";
        $address->state = "GA";
        $address->postalCode = "30501";
        $address->country = "US";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->authorize(1)
                ->withCurrency($this->currency)
                ->withCustomerId(GenerationUtils::getGuid())
                ->withAddress($address)
                ->serialize();

            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    public function testCreditSale()
    {
        $config = $this->config();
        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(1)
                ->withCurrency($this->currency)
                ->withCustomerId(GenerationUtils::getGuid())
                ->withAddress($address)
                ->serialize();
            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);

            $this->assertNotNull($parsedResponse);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    /* 03. ProcessPaymentOtbRequest */

    public function testCreditVerify()
    {
        $config = $this->config();
        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->verify()
                ->withCurrency($this->currency)
                ->withCustomerId(GenerationUtils::getGuid())
                ->withAddress($address)
                ->serialize();
            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);
            $this->assertNotNull($parsedResponse);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    public function testAuthNoAmount()
    {
        $this->expectException(BuilderException::class);
        $this->service->authorize()->withCurrency("USD")->serialize();
    }

    public function testAuthNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $this->service->authorize(10)->serialize();
    }

    public function testSaleNoAmount()
    {
        $this->expectException(BuilderException::class);
        $this->service->charge()->withCurrency("USD")->serialize();
    }

    public function testSaleNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $this->service->charge(10)->serialize();
    }

    public function testVerifyNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $this->service->verify()->serialize();
    }

    public function testVerifyWithAmount()
    {
        $this->expectException(BuilderException::class);
        $this->service->verify()->withAmount(10)->serialize();
    }

    /* 05. CardStorageCreatePayerStoreCardRequest */

    public function testCardStorageCreatePayer()
    {
        $config = $this->config();
        $client = new GpEcomHppClient("secret");

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(15)
                ->withCurrency($this->currency)
                ->withHostedPaymentData($hostedPaymentData)
                ->serialize();

            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);
            $this->assertNotNull($parsedResponse);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    /* 07. CardStorageDisplayStoredCardsRequest */

    public function testCardStorageDisplayStoredCard()
    {
        $config = $this->config();

        $client = new GpEcomHppClient("secret");

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1";
        $hostedPaymentData->customerExists = "1";
        $hostedPaymentData->customerKey = "5e7e9152-2d53-466d-91bc-6d12ebc56b79";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(15)
                ->withCurrency($this->currency)
                ->withHostedPaymentData($hostedPaymentData)
                ->serialize();

            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);
            $this->assertNotNull($parsedResponse);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    /* 09. ContinuousAuthorityRequest */

    public function testContinuousAuthorityRequest()
    {
        $config = $this->config();

        $client = new GpEcomHppClient("secret");

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(15)
                ->withCurrency($this->currency)
                ->withRecurringInfo(RecurringType::FIXED, RecurringSequence::FIRST)
                ->serialize();

            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            $parsedResponse = $service->parseResponse($response);
            $this->assertNotNull($parsedResponse);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    /* 13. DynamicCurrencyConversionRequest */

    public function testEnableDynamicCurrencyConversionRequest()
    {
        $config = $this->config();
        //set config for DCC
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";

        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->hostedPaymentConfig->directCurrencyConversionEnabled = "1";

        $service = new HostedService($config);

        //serialize the request
        $json = $service->Charge(19)
            ->withCurrency($this->currency)
            ->withTimestamp("20170725154824")
            ->withOrderId('GTI5Yxb0SumL_TkDMCAxQA')
            ->serialize();

        $this->assertNotNull($json);
        $this->assertEquals('{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1900","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","DCC_ENABLE":"1","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","HPP_VERSION":"2","SHA1HASH":"448d742db89b05ce97152beb55157c904f3839cc"}', $json);
    }

    public function testDisableDynamicCurrencyConversionRequest()
    {
        //set config for DCC
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";

        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->hostedPaymentConfig->directCurrencyConversionEnabled = "0";

        $service = new HostedService($config);

        //serialize the request
        $json = $service->Charge(19)
            ->withCurrency($this->currency)
            ->withTimestamp("20170725154824")
            ->withOrderId('GTI5Yxb0SumL_TkDMCAxQA')
            ->serialize();

        $this->assertNotNull($json);
        $this->assertEquals('{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1900","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","DCC_ENABLE":"0","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","HPP_VERSION":"2","SHA1HASH":"448d742db89b05ce97152beb55157c904f3839cc"}', $json);
    }

    /* 11. FraudManagementRequest */

    public function testFraudManagementRequest()
    {
        $config = $this->config();
        $config->hostedPaymentConfig->version = 2;
        $config->hostedPaymentConfig->fraudFilterMode = FraudFilterMode::PASSIVE;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerNumber = "E8953893489"; // display the save card tick box
        $hostedPaymentData->productId = "SID9838383"; // new customer

        //serialize the request
        $json = $service->charge(19)
            ->withCurrency($this->currency)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withClientTransactionId("Car Part HV") // varref
            ->withCustomerIpAddress("123.123.123.123")
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();

        $this->assertNotNull($json);

        //make API call
        $response = $client->sendRequest($json, $config->hostedPaymentConfig->version);
        $this->assertNotNull($response);

        $parsedResponse = $service->parseResponse($response);
        $this->assertNotNull($parsedResponse);
        $this->assertEquals("00", $parsedResponse->responseCode);
    }

    /* 11. FraudManagementRequest with fraud rules */

    public function testFraudManagementRequestWithRules()
    {
        $config = $this->config();
        $config->merchantId = 'myMerchantId';
        $config->accountId = 'internet';
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->hostedPaymentConfig->fraudFilterMode = FraudFilterMode::PASSIVE;

        $rules = new FraudRuleCollection();
        $rule1 = '2603986b-3a17-410f-b05a-003f9d955a0f';
        $rule2 = 'a7a0918d-20d7-444f-bf07-65f7d320be91';
        $rules->addRule($rule1, FraudFilterMode::ACTIVE);
        $rules->addRule($rule2, FraudFilterMode::OFF);
        $config->hostedPaymentConfig->fraudFilterRules = $rules;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerNumber = "E8953893489"; // display the save card tick box
        $hostedPaymentData->productId = "SID9838383"; // new customer

        //serialize the request
        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withClientTransactionId("Car Part HV") // varref
            ->withCustomerIpAddress("123.123.123.123")
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();

        $this->assertNotNull($json);

        //make API call
        $response = $client->sendRequest($json, $config->hostedPaymentConfig->version);
        $this->assertNotNull($response);

        $parsedResponse = $service->parseResponse($response);
        $this->assertNotNull($parsedResponse);
        $this->assertEquals("00", $parsedResponse->responseCode);
        $this->assertEquals(FraudFilterMode::PASSIVE, $parsedResponse->responseValues['HPP_FRAUDFILTER_MODE']);
        $this->assertEquals('PASS', $parsedResponse->responseValues['HPP_FRAUDFILTER_RESULT']);
        $this->assertEquals('NOT_EXECUTED', $parsedResponse->responseValues['HPP_FRAUDFILTER_RULE_' . $rule2]);
        $this->assertEquals('PASS', $parsedResponse->responseValues['HPP_FRAUDFILTER_RULE_' . $rule1]);
    }

    /* Serialize methods Test case */

    public function testBasicAuthHppVersion1()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->postDimensions = "https://www.example.com";
        $config->hostedPaymentConfig->postResponse = "https://www.example.com";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_1;

        $service = new HostedService($config);

        $json = $service->authorize(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"TWVyY2hhbnRJZA==","ACCOUNT":"aW50ZXJuZXQ=","ORDER_ID":"R1RJNVl4YjBTdW1MX1RrRE1DQXhRQQ==","AMOUNT":"MTk5OQ==","CURRENCY":"RVVS","TIMESTAMP":"MjAxNzA3MjUxNTQ4MjQ=","AUTO_SETTLE_FLAG":"MA==","HPP_LANG":"R0I=","MERCHANT_RESPONSE_URL":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20vcmVzcG9uc2U=","HPP_VERSION":"MQ==","HPP_POST_DIMENSIONS":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20=","HPP_POST_RESPONSE":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20=","SHA1HASH":"MDYxNjA5Zjg1YThlMDE5MWRjN2Y0ODdmODI3OGU3MTg5OGEyZWUyZA=="}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicAuthHppVersion2()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $json = $service->authorize(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"0","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","SHA1HASH":"061609f85a8e0191dc7f487f8278e71898a2ee2d"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicSale()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","SHA1HASH":"061609f85a8e0191dc7f487f8278e71898a2ee2d"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicHostedPaymentDataHppVersion1()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_1;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->WithHostedPaymentData($hostedPaymentData)
            ->WithDescription("Mobile Channel")
            ->WithClientTransactionId("My Legal Entity")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"TWVyY2hhbnRJZA==","ACCOUNT":"aW50ZXJuZXQ=","ORDER_ID":"R1RJNVl4YjBTdW1MX1RrRE1DQXhRQQ==","AMOUNT":"MTk5OQ==","CURRENCY":"RVVS","TIMESTAMP":"MjAxNzA3MjUxNTQ4MjQ=","AUTO_SETTLE_FLAG":"MQ==","COMMENT1":"TW9iaWxlIENoYW5uZWw=","CUST_NUM":"YTAyODc3NGYtYmVmZi00N2JjLWJkNmUtZWQ3ZTA0ZjVkNzU4YTAyODc3NGYtYnRlZmE=","OFFER_SAVE_CARD":"MQ==","PAYER_EXIST":"MA==","PROD_ID":"YTBiMzhkZjUtYjIzYy00ZDgyLTg4ZmUtMmU5YzQ3NDM4OTcyLWIyM2MtNGQ4Mi04OGY=","VAR_REF":"TXkgTGVnYWwgRW50aXR5","HPP_LANG":"R0I=","MERCHANT_RESPONSE_URL":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20vcmVzcG9uc2U=","HPP_VERSION":"MQ==","SHA1HASH":"NzExNmM0OTgyNjM2N2M2NTEzZWZkYzBjYzgxZTI0M2I4MDk1ZDc4Zg=="}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicHostedPaymentDataHppVersion2()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->WithHostedPaymentData($hostedPaymentData)
            ->WithDescription("Mobile Channel")
            ->WithClientTransactionId("My Legal Entity")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","COMMENT1":"Mobile Channel","CUST_NUM":"a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa","OFFER_SAVE_CARD":"1","PAYER_EXIST":"0","PROD_ID":"a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f","VAR_REF":"My Legal Entity","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","SHA1HASH":"7116c49826367c6513efdc0cc81e243b8095d78f"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testParseResponse()
    {
        $config = $this->config();
        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->authorize(1)
                ->withCurrency($this->currency)
                ->withCustomerId(GenerationUtils::getGuid())
                ->withAddress($address)
                ->serialize();

            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            // Base64 encode values
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($response, true)));
            foreach ($iterator as $key => $value) {
                if (!empty($value)) {
                    $iterator->getInnerIterator()->offsetSet($key, base64_encode($value));
                }
            }

            $response = json_encode($iterator->getArrayCopy());

            $parsedResponse = $service->parseResponse($response, true);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }

    public function testHostedPaymentDataSupplementaryDataSerialize()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->gatewayProvider = GatewayProvider::GP_ECOM;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';
        $hostedPaymentData->supplementaryData = ['HPP_FRAUDFILTER_MODE' => 'ACTIVE'];

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->withOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->withHostedPaymentData($hostedPaymentData)
            ->withDescription("Mobile Channel")
            ->withClientTransactionId("My Legal Entity")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","COMMENT1":"Mobile Channel","CUST_NUM":"a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa","OFFER_SAVE_CARD":"1","PAYER_EXIST":"0","PROD_ID":"a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f","VAR_REF":"My Legal Entity","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_FRAUDFILTER_MODE":"ACTIVE","HPP_VERSION":"2","SHA1HASH":"7116c49826367c6513efdc0cc81e243b8095d78f"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testSupplementaryDataWithOneValueSerialized()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->gatewayProvider = GatewayProvider::GP_ECOM;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->withOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->withHostedPaymentData($hostedPaymentData)
            ->withDescription("Mobile Channel")
            ->withClientTransactionId("My Legal Entity")
            ->withSupplementaryData('HPP_FRAUDFILTER_MODE', 'ACTIVE')
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","COMMENT1":"Mobile Channel","CUST_NUM":"a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa","OFFER_SAVE_CARD":"1","PAYER_EXIST":"0","PROD_ID":"a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f","VAR_REF":"My Legal Entity","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","HPP_FRAUDFILTER_MODE":"ACTIVE","SHA1HASH":"7116c49826367c6513efdc0cc81e243b8095d78f"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testSupplementaryDataWithTwoValuesSerialized()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->gatewayProvider = GatewayProvider::GP_ECOM;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withTimeStamp("20170725154824")
            ->withOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->withHostedPaymentData($hostedPaymentData)
            ->withDescription("Mobile Channel")
            ->withClientTransactionId("My Legal Entity")
            ->withSupplementaryData(["RANDOM_KEY1" => "VALUE_1", "RANDOM_KEY2" => "VALUE_2"])
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","COMMENT1":"Mobile Channel","CUST_NUM":"a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa","OFFER_SAVE_CARD":"1","PAYER_EXIST":"0","PROD_ID":"a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f","VAR_REF":"My Legal Entity","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","RANDOM_KEY1":"VALUE_1","RANDOM_KEY2":"VALUE_2","SHA1HASH":"7116c49826367c6513efdc0cc81e243b8095d78f"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testNetherlandsAntillesCountry()
    {
        $config = $this->config();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";

        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->gatewayProvider = GatewayProvider::GP_ECOM;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';
        $this->billingAddress->country = 'AN';
        $json = $service->charge(19.99)
            ->withCurrency($this->currency)
            ->withHostedPaymentData($hostedPaymentData)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->serialize();

        $response = json_decode($json, true);
        $this->assertEquals('AN', $response['BILLING_CO']);
        $this->assertEquals('530', $response['HPP_BILLING_COUNTRY']);
    }

    public function testCardBlockingPayment()
    {
        $config = $this->config();

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerCountry = 'DE';
        $hostedPaymentData->customerFirstName = 'James';
        $hostedPaymentData->customerLastName = 'Mason';
        $hostedPaymentData->merchantResponseUrl = 'https://www.example.com/returnUrl';
        $hostedPaymentData->transactionStatusUrl = 'https://www.example.com/statusUrl';
        $blockCardTypes = [BlockCardType::COMMERCIAL_CREDIT, BlockCardType::COMMERCIAL_DEBIT];
        $hostedPaymentData->blockCardTypes = $blockCardTypes;

        $json = $service->charge(10.01)
            ->withCurrency($this->currency)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $response = json_decode($json, true);
        $this->assertEquals(implode('|', $blockCardTypes), $response['BLOCK_CARD_TYPE']);

        $client = new GpEcomHppClient("secret");
        $response = $client->sendRequest($json, $config->hostedPaymentConfig->version);
        $parsedResponse = $service->parseResponse($response);

        $this->assertEquals("00", $parsedResponse->responseCode);
    }

    public function testCardBlockingPayment_AllCardTypes()
    {
        $config = $this->config();

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerCountry = 'DE';
        $hostedPaymentData->customerFirstName = 'James';
        $hostedPaymentData->customerLastName = 'Mason';
        $hostedPaymentData->merchantResponseUrl = 'https://www.example.com/returnUrl';
        $hostedPaymentData->transactionStatusUrl = 'https://www.example.com/statusUrl';
        $blockCardTypes = [BlockCardType::CONSUMER_CREDIT, BlockCardType::CONSUMER_DEBIT, BlockCardType::COMMERCIAL_CREDIT, BlockCardType::COMMERCIAL_DEBIT];
        $hostedPaymentData->blockCardTypes = $blockCardTypes;

        $json = $service->charge(10.01)
            ->withCurrency($this->currency)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $response = json_decode($json, true);
        $this->assertEquals(implode('|', $blockCardTypes), $response['BLOCK_CARD_TYPE']);

        $client = new GpEcomHppClient("secret");

        $exceptionCaught = false;
        try {
            $client->sendRequest($json, $config->hostedPaymentConfig->version);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Unexpected Gateway Response: 561 - All card types are blocked, invalid request', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * We can set multiple APMs/LPMs on $presetPaymentMethods, but our HppClient for testing will treat only the first
     * entry from the list as an example for our unit test, in this case will be "sofort"
     */
    public function testBasicChargeAlternativePayment()
    {
        $config = $this->config();

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerCountry = 'DE';
        $hostedPaymentData->customerFirstName = 'James';
        $hostedPaymentData->customerLastName = 'Mason';
        $hostedPaymentData->merchantResponseUrl = 'https://www.example.com/returnUrl';
        $hostedPaymentData->transactionStatusUrl = 'https://www.example.com/statusUrl';

        $apmTypes = [
            AlternativePaymentType::SOFORTUBERWEISUNG,
            AlternativePaymentType::TEST_PAY,
            AlternativePaymentType::PAYPAL,
            AlternativePaymentType::SEPA_DIRECTDEBIT_PPPRO_MANDATE_MODEL_A
        ];
        $hostedPaymentData->presetPaymentMethods = $apmTypes;

        $json = $service->charge(10.01)
            ->withCurrency($this->currency)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();

        $response = json_decode($json, true);
        $this->assertEquals(implode('|', $apmTypes), $response['PM_METHODS']);
        $this->assertEquals($hostedPaymentData->customerFirstName, $response['HPP_CUSTOMER_FIRSTNAME']);
        $this->assertEquals($hostedPaymentData->customerLastName, $response['HPP_CUSTOMER_LASTNAME']);
        $this->assertEquals($hostedPaymentData->merchantResponseUrl, $response['MERCHANT_RESPONSE_URL']);
        $this->assertEquals($hostedPaymentData->transactionStatusUrl, $response['HPP_TX_STATUS_URL']);
        $this->assertEquals($hostedPaymentData->customerCountry, $response['HPP_CUSTOMER_COUNTRY']);

        $client = new GpEcomHppClient("secret");
        $response = $client->sendRequest($json, $config->hostedPaymentConfig->version);
        $parsedResponse = $service->parseResponse($response);

        $this->assertNotNull($parsedResponse);
        $this->assertEquals("01", $parsedResponse->responseCode);
        $this->assertEquals(TransactionStatus::PENDING, $parsedResponse->responseMessage);
        $this->assertEquals(AlternativePaymentType::SOFORTUBERWEISUNG, $parsedResponse->responseValues['PAYMENTMETHOD']);
        $this->assertEquals($hostedPaymentData->merchantResponseUrl, $parsedResponse->responseValues['MERCHANT_RESPONSE_URL']);
    }

    public function testCaptureBillingShippingInfo()
    {
        $config = $this->config();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->addressCapture = true;
        $hostedPaymentData->notReturnAddress = false;
        $hostedPaymentData->removeShipping = true;

        $json = $service->charge(19)
            ->withCurrency($this->currency)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $response = json_decode($json, true);
        $this->assertTrue($response['HPP_CAPTURE_ADDRESS']);
        $this->assertFalse($response['HPP_DO_NOT_RETURN_ADDRESS']);
        $this->assertTrue($response['HPP_REMOVE_SHIPPING']);
    }

    public function testOpenBankingInitiate()
    {
        $config = $this->config();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->shaHashType = ShaHashType::SHA256;

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerCountry = 'DE';
        $hostedPaymentData->customerFirstName = 'James';
        $hostedPaymentData->customerLastName = 'Mason';
        $hostedPaymentData->transactionStatusUrl = 'https://www.example.com/statusUrl';
        $hostedPaymentData->merchantResponseUrl = 'https://www.example.com/statusUrl';
        $hostedPaymentData->presetPaymentMethods = [HostedPaymentMethods::OB];

        $bankPayment = new BankPayment();
        $bankPayment->accountNumber = '12345678';
        $bankPayment->sortCode = '406650';
        $bankPayment->accountName = 'AccountName';
        $hostedPaymentData->bankPayment = $bankPayment;


        $client = new GpEcomHppClient($config->sharedSecret, ShaHashType::SHA256);
        $service = new HostedService($config);

        $json = $service->charge(10.99)
            ->withCurrency("GBP")
            ->withHostedPaymentData($hostedPaymentData)
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->serialize();
        $this->assertNotNull($json);
        $response = $client->sendRequest($json, HppVersion::VERSION_2);
        $this->assertNotNull($response);

        $parsedResponse = $service->parseResponse($response);
        $this->assertEquals(BankPaymentStatus::PAYMENT_INITIATED, $parsedResponse->responseMessage);
    }

    public function test3DSExemption()
    {
        $config = $this->config();
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->enableExemptionOptimization = true;
        $hostedPaymentData->challengeRequest = ChallengeRequestIndicator::NO_CHALLENGE_REQUESTED;

        //serialize the request
        $json = $service->charge(10.01)
            ->withCurrency($this->currency)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $this->assertNotNull($json);

        $jsonResponse = json_decode($json, true);
        $this->assertTrue(isset($jsonResponse['HPP_ENABLE_EXEMPTION_OPTIMIZATION']));
        $this->assertTrue($jsonResponse['HPP_ENABLE_EXEMPTION_OPTIMIZATION']);
    }

    /* 10. ThreedSecureResponse */

    public function testThreeDSecureResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        //response
        // TODO: grab the response JSON from the client-side for example:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"hpp","ORDER_ID":"OTA4NUEzOEEtMkE3RjU2RQ","TIMESTAMP":"20180724124150","RESULT":"00","PASREF":"15324325098818233","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":"123|56","BILLING_CO":"IRELAND","ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":null,"SHA1HASH":"d1ff806b449b86375dbda74e2611760c348fcdeb","DCC_INFO_REQUST":null,"DCC_INFO_RESPONSE":null,"HPP_FRAUDFILTER_MODE":null,"TSS_INFO":null}';

        $parsedResponse = $service->parseResponse($responseJson);
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $eci = $responseValues["ECI"]; // 5 - fully authenticated
        $cavv = $responseValues["CAVV"]; // AAACBUGDZYYYIgGFGYNlAAAAAAA=
        $xid = $responseValues["XID"]; // vJ9NXpFueXsAqeb4iAbJJbe+66s=
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 02. ProcessPaymentConsumeHppResponse */

    public function testProcessPaymentConsumeResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"hpp","ORDER_ID":"NjMwNkMxMTAtMTA5RUNDRQ","TIMESTAMP":"20180720104340","RESULT":"00","PASREF":"15320798200414985","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":"123|56","BILLING_CO":"IRELAND","ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"100","SHA1HASH":"32628cf3f887ab9f4f1c547a10ac365c2168f0e2","DCC_INFO":null,"HPP_FRAUDFILTER_MODE":null,"TSS_INFO":null}';

        // create the response object from the response JSON
        $parsedResponse = $service->parseResponse($responseJson);

        $orderId = $parsedResponse->orderId; // GTI5Yxb0SumL_TkDMCAxQA
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        //$fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // PASS

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 06. CardStorageCreatePayerStoreCardResponse */

    public function testCardStorageCreatePayerStoreCardResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"3dsecure","ORDER_ID":"NTgxMkMzODUtNTEwMkNCMw","TIMESTAMP":"20180723110112","RESULT":"00","PASREF":"15323400720177562","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":null,"BILLING_CO":null,"ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"1500","SHA1HASH":"4c7a635401c57371a0931bb3a21a849181cc963d","DCC_INFO":null,"HPP_FRAUDFILTER_MODE":null,"TSS_INFO":null}';

        $parsedResponse = $service->parseResponse($responseJson);
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        /*
          // Payer Setup Details
          $payerSetupResult = $responseValues["PAYER_SETUP"]; // 00
          $payerSetupMessage = $responseValues["PAYER_SETUP_MSG"]; // Successful
          $payerReference = $responseValues["SAVED_PAYER_REF"]; // 5e7e9152-2d53-466d-91bc-6d12ebc56b79
          // Card Setup Details
          $cardSetupResult = $responseValues["PMT_SETUP"]; // 00
          $cardSetupMessage = $responseValues["PMT_SETUP_MSG"]; // Successful
          $cardReference = $responseValues["SAVED_PMT_REF"]; // ca68dcac-9af2-4d65-b06c-eb54667dcd4a
          // Card Details Stored
          $cardType = $responseValues["SAVED_PMT_TYPE"]; // MC
          $cardDigits = $responseValues["SAVED_PMT_DIGITS"]; // 542523xxxx4415
          $cardExpiry = $responseValues["SAVED_PMT_EXPDATE"]; // 1025
          $cardName = $responseValues["SAVED_PMT_NAME"]; // James Mason
         */
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 08. CardStorageDisplayStoredCardsResponse */

    public function testCardStorageDisplayStoredCardsResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = array("MERCHANT_ID" => "MerchantId", "ACCOUNT" => "internet", "MERCHANT_RESPONSE_URL" => "https://requestb.in/10q2bjb1", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "843680654f377bfa845387fdbace35acc9d95778", "RESULT" => "00", "AUTHCODE" => "12345", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642");

        $parsedResponse = $service->parseResponse(json_encode($responseJson));
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        // card used to complete payment, edited or deleted
        $chosenCard = $responseValues["HPP_CHOSEN_PMT_REF"]; // 099efeb4-eda2-4fd7-a04d-29647bb6c51d
        $editedCard = $responseValues["HPP_EDITED_PMT_REF"]; // 037bd26a-c76b-4ee4-8063-376d8858f23d
        $deletedCard = $responseValues["HPP_DELETED_PMT_REF"]; // 3db4c72c-cd95-4743-8070-f17e2b56b642
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 12. FraudManagementResponse */

    public function testFraudManagementResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = array("MERCHANT_ID" => "MerchantId", "ACCOUNT" => "internet", "MERCHANT_RESPONSE_URL" => "https://requestb.in/10q2bjb1", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "843680654f377bfa845387fdbace35acc9d95778", "RESULT" => "00", "AUTHCODE" => "12345", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "HOLD", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS");

        $parsedResponse = $service->parseResponse(json_encode($responseJson));
        $responseCode = $parsedResponse->responseCode; // 00
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // HOLD
        $cardRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe"]; // HOLD
        $ipRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305"]; // PASS
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 14. DynamicCurrencyConversionResponse */

    public function testDynamicCurrencyConversionResponse()
    {
        $config = $this->config();
        $service = new HostedService($config);

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"apidcc","ORDER_ID":"NTQyQzgxREMtMzVFQzlDNw","TIMESTAMP":"20180724095953","RESULT":"00","PASREF":"15324227932436743","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":null,"BILLING_CO":null,"ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"100100","SHA1HASH":"320c7ddc49d292f5900c676168d5cc1f2a55306c","DCC_INFO_REQUST":{"CCP":"Fexco","TYPE":1,"RATE":"1.7203","RATE_TYPE":"S","AMOUNT":"172202","CURRENCY":"AUD"},"DCC_INFO_RESPONSE":{"cardHolderCurrency":"AUD","cardHolderAmount":"172202","cardHolderRate":"1.7203","merchantCurrency":"EUR","merchantAmount":"100100","marginRatePercentage":"","exchangeRateSourceName":"","commissionPercentage":"","exchangeRateSourceTimestamp":""},"HPP_FRAUDFILTER_MODE":null,"TSS_INFO":null}';
        $parsedResponse = $service->parseResponse($responseJson);

        $responseCode = $parsedResponse->responseCode; // 00
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $conversionProcessor = $responseValues['DCC_INFO_REQUST']["CCP"]; // fexco
        $conversionRate = $responseValues['DCC_INFO_REQUST']["RATE"]; // 1.7203
        $merchantAmount = $responseValues['DCC_INFO_RESPONSE']["merchantAmount"]; // 1999
        $cardholderAmount = $responseValues['DCC_INFO_RESPONSE']["cardHolderAmount"]; // 3439
        $merchantCurrency = $responseValues['DCC_INFO_RESPONSE']["merchantCurrency"]; // EUR
        $cardholderCurrency = $responseValues['DCC_INFO_RESPONSE']["cardHolderCurrency"]; // AUD
        $marginPercentage = $responseValues['DCC_INFO_RESPONSE']["marginRatePercentage"]; // 3.75
        $exchangeSource = $responseValues['DCC_INFO_RESPONSE']["exchangeRateSourceName"]; // REUTERS WHOLESALE INTERBANK
        $commissionPercentage = $responseValues['DCC_INFO_RESPONSE']["commissionPercentage"]; // 0
        $exchangeTimestamp = $responseValues['DCC_INFO_RESPONSE']["exchangeRateSourceTimestamp"]; // 20170518162700
        // TODO: update your application and display transaction outcome to the customer
        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    public function testCheckHashVulnerability()
    {
        $config = $this->config();
        $service = new HostedService($config);

        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"hpp","ORDER_ID":"NjMwNkMxMTAtMTA5RUNDRQ","TIMESTAMP":"20180720104340","RESULT":"00","PASREF":"15320798200414985","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":"123|56","BILLING_CO":"IRELAND","ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"https:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"100","SHA1HASH":true,"DCC_INFO":null,"HPP_FRAUDFILTER_MODE":null,"TSS_INFO":null}';

        $exceptionCaught = false;
        try {
            $service->parseResponse($responseJson);
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Incorrect hash. Please check your code and the Developers Documentation.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}
