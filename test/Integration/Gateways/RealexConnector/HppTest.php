<?php

namespace GlobalPayments\Api\Test\Integration\Gateways\RealexConnector;

// using GlobalPayments.Api.Entities;
// using GlobalPayments.Api.Services;
// using GlobalPayments.Api.Tests.Realex.Hpp;
// using Microsoft.VisualStudio.TestTools.UnitTesting;

use PHPUnit\Framework\TestCase;

class HppTest extends TestCase
{
    /** @var HostedService */
    protected $service;
    /** @var RealexHppClient */
    protected $client;

    protected function config()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        return $config;
    }

    public function setup()
    {
        // $client = new RealexHppClient("https://pay.sandbox.realexpayments.com/pay", "secret");
        // $service = new HostedService(new ServicesConfig {
        //     MerchantId = "heartlandgpsandbox",
        //     AccountId = "hpp",
        //     SharedSecret = "secret",
        //     ServiceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi",
        //     HostedPaymentConfig = new HostedPaymentConfig {
        //         Language = "GB",
        //         ResponseUrl = "http://requestb.in/10q2bjb1"
        //     }
        // });
    }

    public function testCreditAuth()
    {
        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        $json = $service->Authorize(1)
            ->withCurrency("EUR")
            ->withCustomerId("123456")
            ->withAddress($address)
            ->serialize();
        $this->assertNotNull($json);

        $response = $client->sendRequest($json);
        $parsedResponse = $service->parseResponse($response);
        $this->assertNotNull($response);
        $this->assertEquals("00", $parsedResponse->responseCode);
    }

    public function testCreditSale()
    {
        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        $json = $service->charge(1)
            ->withCurrency("EUR")
            ->withCustomerId("123456")
            ->withAddress($address)
            ->serialize();
        $this->assertNotNull($json);

        $response = $client->sendRequest($json);
        $parsedResponse = $service->parseResponse($response);
        $this->assertNotNull($response);
        $this->assertEquals("00", $parsedResponse->responseCode);
    }

    public function testCreditVerify()
    {
        $address = new Address();
        $address->postalCode = "123|56";
        $address->country = "IRELAND";

        $json = $service->verify()
            ->withCurrency("EUR")
            ->withCustomerId("123456")
            ->withAddress($address)
            ->serialize();
        $this->assertNotNull($json);

        $response = $client->sendRequest($json);
        $parsedResponse = $service->parseResponse($response);
        $this->assertNotNull($response);
        $this->assertEquals("00", $parsedResponse->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuthNoAmount()
    {
        $service->Authorize(null)->withCurrency("USD")->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuthNoCurrency()
    {
        $service->Authorize(10)->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSaleNoAmount()
    {
        $service->charge(null)->withCurrency("USD")->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSaleNoCurrency()
    {
        $service->charge(10)->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifyNoCurrency()
    {
        $service->verify()->serialize();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifyWithAmount()
    {
        $service->verify()->withAmount(10)->serialize();
    }
}
