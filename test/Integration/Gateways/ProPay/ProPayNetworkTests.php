<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay;

use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

class ProPayNetworkTests extends TestCase
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
    
    public function testDisburseFunds()
    {
        // This method in the ProPay API requires a different, special CertificationStr value from a disbursement account
        $response = PayFacService::disburseFunds()
        ->withReceivingAccountNumber("718136438")
        ->withAmount("100")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    public function testSpendBackTransaction()
    {
        $response = PayFacService::spendBack()
        ->withAccountNumber("718037672")
        ->withReceivingAccountNumber("718136438")
        ->withAmount("100")
        ->withAllowPending(false)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testReverseSplitPay()
    {
        $response = PayFacService::reverseSplitPay()
        ->withAccountNumber("718136438")
        ->withAmount("3")
        ->withCCAmount("1")
        ->withRequireCCRefund(false)
        ->withTransNum("35")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
        $this->assertNotNull($response->payFacData->amount);
        $this->assertNotNull($response->payFacData->recAccountNum);
        $this->assertNotNull($response->payFacData->transNum);
    }
    
    public function testSplitFunds()
    {
        $response = PayFacService::splitFunds()
        ->withAccountNumber("718136438")
        ->withReceivingAccountNumber("718134204")
        ->withAmount("3")
        ->withTransNum("35")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
