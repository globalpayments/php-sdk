<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay;

use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

class ProPayGetInformationTests extends TestCase
{

    public function setup(): void
    {
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->certificationStr = 'a0287011dbb4181a29a5f07de995b9';
        $config->terminalId = 'e995b9';
        $config->environment = Environment::TEST;
        $config->selfSignedCertLocation = __DIR__ . '/TestData/selfSignedCertificate.crt';
        return $config;
    }

    public function testGetAccountInfo()
    {
        $response = PayFacService::getAccountDetails()
            ->withAccountNumber("718136438")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }


    public function testGetAccountBalance()
    {
        $response = PayFacService::getAccountBalance()
            ->withAccountNumber("718136438")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
        $this->assertNotNull($response->payFacData->flashFunds);
        $this->assertNotNull($response->payFacData->aCHOut);
    }

    public function testGetAccountInfoExternalId()
    {
        $response = PayFacService::getAccountDetails()
            ->withExternalId("1")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }


    public function testGetAccountInfoSourceEmail()
    {
        $response = PayFacService::getAccountDetails()
            ->withSourceEmail("user4804@user.com")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
