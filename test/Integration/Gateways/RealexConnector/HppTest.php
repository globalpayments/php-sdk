<?php

namespace GlobalPayments\Api\Test\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector\Hpp\RealexHppClient;
use PHPUnit\Framework\TestCase;

class HppTest extends TestCase
{

    private $hppVersionList = [
        HppVersion::VERSION_1,
        HppVersion::VERSION_2,
        ''
    ];

    public function basicSetup()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        return new HostedService($config);
    }

    public function testCreditAuth()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuthNoAmount()
    {
        $service = $this->basicSetup();
        $service->authorize(null)->withCurrency("USD")->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuthNoCurrency()
    {
        $service = $this->basicSetup();
        $service->authorize(10)->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSaleNoAmount()
    {
        $service = $this->basicSetup();
        $service->charge(null)->withCurrency("USD")->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSaleNoCurrency()
    {
        $service = $this->basicSetup();
        $service->charge(10)->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifyNoCurrency()
    {
        $service = $this->basicSetup();
        $service->verify()->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifyWithAmount()
    {
        $service = $this->basicSetup();
        $service->verify()->withAmount(10)->serialize();
    }

    /* 05. CardStorageCreatePayerStoreCardRequest */

    public function testCardStorageCreatePayer()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

        //serialize the request
        $json = $service->Charge(19)
                ->withCurrency("EUR")                
                ->withTimestamp("20170725154824")
                ->withOrderId('GTI5Yxb0SumL_TkDMCAxQA')
                ->serialize();
        
        $this->assertNotNull($json);
        $this->assertEquals($json, '{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1900","CURRENCY":"EUR","TIMESTAMP":"20170725154824","AUTO_SETTLE_FLAG":"1","DCC_ENABLE":"0","HPP_LANG":"GB","MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","HPP_VERSION":"2","SHA1HASH":"448d742db89b05ce97152beb55157c904f3839cc"}');
    }

    /* 11. FraudManagementRequest */

    public function testFraudManagementRequest()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        $config->hostedPaymentConfig->version = 2;
        $config->hostedPaymentConfig->FraudFilterMode = FraudFilterMode::PASSIVE;

        $service = new HostedService($config);
        $client = new RealexHppClient("secret");

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "50001|Flat 123";
        $billingAddress->country = "US";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "654|123";
        $shippingAddress->country = "GB";
        
        // data to be passed to the HPP along with transaction level settings
        $hostedPaymentData = new HostedPaymentData();
        $hostedPaymentData->customerNumber = "E8953893489"; // display the save card tick box
        $hostedPaymentData->productId = "SID9838383"; // new customer

        //serialize the request
        $json = $service->charge(19)
                ->withCurrency("EUR")
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withAddress($shippingAddress, AddressType::SHIPPING)
                //->withProductId("SID9838383") // prodid
                ->withClientTransactionId("Car Part HV") // varref
                //->withCustomerId("E8953893489") // custnum
                ->withCustomerIpAddress("123.123.123.123")
                //->withFraudFilter(FraudFilterMode::PASSIVE)
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

    /* Serialize methods Test case */

    public function testBasicAuthHppVersion1()
    {
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

        $json = $service->authorize(19.99)
                ->withCurrency("EUR")
                ->withTimeStamp("20170725154824")
                ->WithOrderId("GTI5Yxb0SumL_TkDMCAxQA")
                ->serialize();

        $expectedJson = '{"MERCHANT_ID":"TWVyY2hhbnRJZA==","ACCOUNT":"aW50ZXJuZXQ=","ORDER_ID":"R1RJNVl4YjBTdW1MX1RrRE1DQXhRQQ==","AMOUNT":"MTk5OQ==","CURRENCY":"RVVS","TIMESTAMP":"MjAxNzA3MjUxNTQ4MjQ=","AUTO_SETTLE_FLAG":"MA==","HPP_LANG":"R0I=","MERCHANT_RESPONSE_URL":"aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20vcmVzcG9uc2U=","HPP_VERSION":"MQ==","SHA1HASH":"MDYxNjA5Zjg1YThlMDE5MWRjN2Y0ODdmODI3OGU3MTg5OGEyZWUyZA=="}';
        $this->assertEquals($json, $expectedJson);
    }

    public function testBasicAuthHppVersion2()
    {
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
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
        $client = new RealexHppClient("secret");

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
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";

        $client = new RealexHppClient("secret");

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
                $iterator->getInnerIterator()->offsetSet($key, base64_encode($value));
            }

            $response = json_encode($iterator->getArrayCopy());

            $parsedResponse = $service->parseResponse($response, true);
            $this->assertEquals("00", $parsedResponse->responseCode);
        }
    }
}
