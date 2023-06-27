<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay;

use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

class ProPayNetworkTests extends TestCase
{
    public function setup() : void
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

    public function testDisburseFunds()
    {
        // This transaction required additional configuration needed from ProPay | Fund should be available in account
        // This method in the ProPay API requires a different, special CertificationStr value from a disbursement account
        $response = PayFacService::disburseFunds()
        ->withReceivingAccountNumber("718583641")
        ->withAmount("1")
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSpendBackTransaction()
    {
        $response = PayFacService::spendBack()
        ->withAccountNumber("718581374")
        ->withReceivingAccountNumber("718581374")
        ->withAmount("100")
        ->withAllowPending(false)
        ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testReverseSplitPay()
    {
        $response = PayFacService::reverseSplitPay()
        ->withAccountNumber("718581375")
        ->withAmount("10")
        ->withCCAmount("0")
        ->withRequireCCRefund(true)
        ->withTransNum("7")
        ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
        $this->assertNotNull($response->payFacData->amount);
        $this->assertNotNull($response->payFacData->recAccountNum);
        $this->assertNotNull($response->payFacData->transNum);
    }

    public function testSplitFunds()
    {
        // This transaction required additional configuration needed from ProPay
        $response = PayFacService::splitFunds()
        ->withAccountNumber("718581374")
        ->withReceivingAccountNumber("718581375")
        ->withAmount("10")
        // ->withTransNum("1")
        // ->withGatewayTransactionId("2814958059")
        // ->withCardBrandTransactionId("SURN3DP8G1108")
        ->withGlobaltransId("719D277C-C36D-4886-B79F-E54BA919CAC4")
        ->withGlobalTransSource("portico")
        ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
