<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay;

use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData\TestFundsData;

class ProPayFundsTests extends TestCase
{
    public function setup(): void
    {
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->certificationStr = '4ee64cbd706400fb4a34e65aab6f48';
        $config->terminalId = 'ab6f48';
        $config->environment = Environment::TEST;
        $config->selfSignedCertLocation = __DIR__ . '/TestData/selfSignedCertificate.crt';
        return $config;
    }

    public function testAddFunds()
    {
        $response = PayFacService::addFunds()
            ->withAccountNumber("718134204")
            ->withAmount("10")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSweepFunds()
    {
        $response = PayFacService::sweepFunds()
            ->withAccountNumber("718570822")
            ->withAmount("10")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAddFlashFundsPaymentCard()
    {
        // It can only be tested in production as required additional configuration from propay
        $response = PayFacService::addCardFlashFunds()
            ->withAccountNumber("718136438")
            ->withFlashFundsPaymentCardData(TestFundsData::getFlashFundsData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testPushMoneyToFlashFundsCard()
    {
        // It can only be tested in production as required additional configuration from propay
        $response = PayFacService::pushMoneyToFlashFundsCard()
            ->withAccountNumber("718136438")
            ->withAmount("100")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
