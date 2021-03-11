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
    
    public function setup()
    {
        ServicesContainer::configureService($this->getConfig());
    }
        
    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->certificationStr = '5dbacb0fc504dd7bdc2eadeb7039dd';
        $config->terminalId = '7039dd';
        $config->environment = Environment::TEST;
        $config->selfSignedCertLocation = __DIR__ . '/TestData/selfSignedCertificate.crt';
        return $config;
    }
    
    public function testAddFunds()
    {
        $response = PayFacService::addFunds()
        ->withAccountNumber("718136438")
        ->withAmount("300")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    
    public function testSweepFunds()
    {
        $response = PayFacService::sweepFunds()
        ->withAccountNumber("718136438")
        ->withAmount("10")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testAddFlashFundsPaymentCard()
    {
        $response = PayFacService::addCardFlashFunds()
        ->withAccountNumber("718136438")
        ->withFlashFundsPaymentCardData(TestFundsData::getFlashFundsData())
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testPushMoneyToFlashFundsCard()
    {
        $response = PayFacService::pushMoneyToFlashFundsCard()
        ->withAccountNumber("718136438")
        ->withAmount("100")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
