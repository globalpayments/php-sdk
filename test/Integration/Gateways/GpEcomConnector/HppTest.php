<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\BankPaymentStatus;
use GlobalPayments\Api\Entities\Enums\HostedPaymentMethods;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\FraudRuleCollection;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector\Hpp\GpEcomHppClient;
use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\ChallengeRequestIndicator;
use PHPUnit\Framework\TestCase;

class HppTest extends TestCase
{
    private $billingAddress;

    private $shippingAddress;

    private $hppVersionList = [
        HppVersion::VERSION_1,
        HppVersion::VERSION_2,
        ''
    ];

    public function setup() : void
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
    }

    public function basicSetup()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        return new HostedService($config);
    }

    public function testCreditAuth()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->authorize(1)
                    ->withCurrency("EUR")
                    ->withCustomerId("123456")
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(1)
                    ->withCurrency("EUR")
                    ->withCustomerId("123456")
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->verify()
                    ->withCurrency("EUR")
                    ->withCustomerId("123456")
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
        $service = $this->basicSetup();
        $service->authorize(null)->withCurrency("USD")->serialize();
    }

    public function testAuthNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $service = $this->basicSetup();
        $service->authorize(10)->serialize();
    }

    public function testSaleNoAmount()
    {
        $this->expectException(BuilderException::class);
        $service = $this->basicSetup();
        $service->charge(null)->withCurrency("USD")->serialize();
    }

    public function testSaleNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $service = $this->basicSetup();
        $service->charge(10)->serialize();
    }

    public function testVerifyNoCurrency()
    {
        $this->expectException(BuilderException::class);
        $service = $this->basicSetup();
        $service->verify()->serialize();
    }

    public function testVerifyWithAmount()
    {
        $this->expectException(BuilderException::class);
        $service = $this->basicSetup();
        $service->verify()->withAmount(10)->serialize();
    }

    /* 05. CardStorageCreatePayerStoreCardRequest */

    public function testCardStorageCreatePayer()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

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
                    ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

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
                    ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new GpEcomHppClient("secret");

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->charge(15)
                    ->withCurrency("EUR")
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
        //set config for DCC
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->hostedPaymentConfig->directCurrencyConversionEnabled = "1";

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        //serialize the request
        $json = $service->Charge(19)
                ->withCurrency("EUR")
                ->withTimestamp("20170725154824")
                ->withOrderId('GTI5Yxb0SumL_TkDMCAxQA')
                ->serialize();
        
        $this->assertNotNull($json);
        $this->assertEquals($json, '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1900","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","DCC_ENABLE":"1","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","HPP_VERSION":"2","SHA1HASH":"448d742db89b05ce97152beb55157c904f3839cc"}');
    }
    
    public function testDisableDynamicCurrencyConversionRequest()
    {
        //set config for DCC
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->hostedPaymentConfig->directCurrencyConversionEnabled = "0";

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        //serialize the request
        $json = $service->Charge(19)
                ->withCurrency("EUR")
                ->withTimestamp("20170725154824")
                ->withOrderId('GTI5Yxb0SumL_TkDMCAxQA')
                ->serialize();
        
        $this->assertNotNull($json);
        $this->assertEquals($json,  '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1900","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","DCC_ENABLE":"0","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","HPP_VERSION":"2","SHA1HASH":"448d742db89b05ce97152beb55157c904f3839cc"}');
    }

    /* 11. FraudManagementRequest */

    public function testFraudManagementRequest()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
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
                ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = 'myMerchantId';
        $config->accountId = 'internet';
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
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
            ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->postDimensions = "https://www.example.com";
        $config->hostedPaymentConfig->postResponse = "https://www.example.com";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_1;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $json = $service->authorize(19.99)
                ->withCurrency("EUR")
                ->withTimeStamp("20170725154824")
                ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
                ->serialize();

        $expectedJson = '{"MERCHANT_ID":"TWVyY2hhbnRJZA==","ACCOUNT":"aW50ZXJuZXQ=","ORDER_ID":"R1RJNVl4YjBTdW1MX1RrRE1DQXhRQQ==","AMOUNT":"MTk5OQ==","CURRENCY":"RVVS","TIMESTAMP":"MjAxNzA3MjUxNTQ4MjQ=","AUTO_SETTLE_FLAG":"MA==","HPP_LANG":"R0I=","MERCHANT_RESPONSE_URL":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20vcmVzcG9uc2U=","HPP_VERSION":"MQ==","HPP_POST_DIMENSIONS":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20=","HPP_POST_RESPONSE":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20=","SHA1HASH":"MDYxNjA5Zjg1YThlMDE5MWRjN2Y0ODdmODI3OGU3MTg5OGEyZWUyZA=="}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicAuthHppVersion2()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $json = $service->authorize(19.99)
                ->withCurrency("EUR")
                ->withTimeStamp("20170725154824")
                ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
                ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"0","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","SHA1HASH":"061609f85a8e0191dc7f487f8278e71898a2ee2d"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicSale()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $json = $service->charge(19.99)
                ->withCurrency("EUR")
                ->withTimeStamp("20170725154824")
                ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
                ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_VERSION":"2","SHA1HASH":"061609f85a8e0191dc7f487f8278e71898a2ee2d"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicHostedPaymentDataHppVersion1()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_1;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
                ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->offerToSaveCard = "1"; // display the save card tick box
        $hostedPaymentData->customerExists = "0"; // new customer
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';

        $json = $service->charge(19.99)
                ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new GpEcomHppClient("secret");

        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        //run test cases for different version
        foreach ($this->hppVersionList as $hppVersion) {
            $config->hostedPaymentConfig->version = $hppVersion;
            $service = new HostedService($config);

            $json = $service->authorize(1)
                    ->withCurrency("EUR")
                    ->withCustomerId("123456")
                    ->withAddress($address)
                    ->serialize();
            
            $this->assertNotNull($json);

            $response = $client->sendRequest($json, $hppVersion);
            $this->assertNotNull($response);

            // Base64 encode values
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator(json_decode($response, true)));
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
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
            ->withCurrency("EUR")
            ->withTimeStamp("20170725154824")
            ->withOrderId("GTI5Yxb0SumL_TkDMCAxQA")
            ->withHostedPaymentData($hostedPaymentData)
            ->withDescription("Mobile Channel")
            ->withClientTransactionId("My Legal Entity")
            ->serialize();

        $expectedJson = '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","COMMENT1":"Mobile Channel","CUST_NUM":"a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa","OFFER_SAVE_CARD":"1","PAYER_EXIST":"0","PROD_ID":"a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f","VAR_REF":"My Legal Entity","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"https:\/\/www.example.com\/response","HPP_FRAUDFILTER_MODE":"ACTIVE","HPP_VERSION":"2","SHA1HASH":"7116c49826367c6513efdc0cc81e243b8095d78f"}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testSupplementaryDataWithOneValueSerialized() {
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
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
            ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
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
            ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;
        $config->gatewayProvider = GatewayProvider::GP_ECOM;

        $service = new HostedService($config);

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerNumber = 'a028774f-beff-47bc-bd6e-ed7e04f5d758a028774f-btefa';
        $hostedPaymentData->productId = 'a0b38df5-b23c-4d82-88fe-2e9c47438972-b23c-4d82-88f';
        $this->billingAddress->country = 'AN';
        $json = $service->charge(19.99)
            ->withCurrency("EUR")
            ->withHostedPaymentData($hostedPaymentData)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->serialize();

        $response = json_decode($json, true);
        $this->assertEquals('AN', $response['BILLING_CO']);
        $this->assertEquals('530', $response['HPP_BILLING_COUNTRY']);
    }

    /**
     * We can set multiple APMs/LPMs on $presetPaymentMethods, but our HppClient for testing will treat only the first
     * entry from the list as an example for our unit test, in this case will be "sofort"
     */
    public function testBasicChargeAlternativePayment()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";

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
            ->withCurrency("EUR")
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
        $config = new GpEcomConfig();
        $config->merchantId = "MerchantId";
        $config->accountId = "internet";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);
        $client = new GpEcomHppClient("secret");

        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->addressCapture = true;
        $hostedPaymentData->notReturnAddress = false;

        $json = $service->charge(19)
            ->withCurrency("EUR")
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $response = json_decode($json, true);
        $this->assertEquals(true, $response['HPP_CAPTURE_ADDRESS']);
        $this->assertEquals(false, $response['HPP_DO_NOT_RETURN_ADDRESS']);
    }

    public function testOpenBankingInitiate()
    {
        $config = new GpEcomConfig();
        $config->merchantId = 'openbankingsandbox';
        $config->sharedSecret = 'sharedsecret';
        $config->accountId = 'internet';
        $config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
        $config->enableBankPayment = true;
        $config->hostedPaymentConfig = new HostedPaymentConfig();
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
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->sharedSecret = "secret";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "https://www.example.com/response";;
        $config->hostedPaymentConfig->version = HppVersion::VERSION_2;

        $service = new HostedService($config);

        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->enableExemptionOptimization = true;
        $hostedPaymentData->challengeRequest = ChallengeRequestIndicator::NO_CHALLENGE_REQUESTED;

        //serialize the request
        $json = $service->charge(10.01)
            ->withCurrency("EUR")
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withHostedPaymentData($hostedPaymentData)
            ->serialize();
        $this->assertNotNull($json);

        $jsonResponse = json_decode($json, true);
        $this->assertTrue(isset($jsonResponse['HPP_ENABLE_EXEMPTION_OPTIMIZATION']));
        $this->assertEquals(true, $jsonResponse['HPP_ENABLE_EXEMPTION_OPTIMIZATION']);
    }
}
