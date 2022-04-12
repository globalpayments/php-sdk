<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;
use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;

class UpaCreditTests extends TestCase
{

    private $device;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown()
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.213.79';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_SATURN_1000;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new LogManagement();

        return $config;
    }

    public function testCreditSaleSwipe()
    {
        $response = $this->device->creditSale(10)
            ->withAllowDuplicates(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }


    /*
     * Note: EMV cards needs to be used for this test case
     */
    public function testCreditSaleEMV()
    {
        $response = $this->device->creditSale(10)
            ->withAllowDuplicates(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        //EMV
        $this->assertNotNull($response->applicationPreferredName);
        $this->assertNotNull($response->applicationLabel);
        $this->assertNotNull($response->applicationId);
        $this->assertNotNull($response->applicationCryptogramType);
        $this->assertNotNull($response->applicationCryptogram);
        $this->assertNotNull($response->customerVerificationMethod);
        $this->assertNotNull($response->terminalVerificationResults);
    }

    public function testCreditVoid()
    {
        $response = $this->device->creditSale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);

        $refundResponse = $this->device->creditVoid()
            ->withTerminalRefNumber($response->terminalRefNumber)
            ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }

    public function testSaleRefund()
    {
        $refundResponse = $this->device->creditRefund(10)
            ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }

    public function testCreditTipAdjust()
    {
        $response = $this->device->creditSale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $adjust = $this->device->creditTipAdjust(1.05)
            ->withTerminalRefNumber($response->terminalRefNumber)
            ->execute();

        $this->assertNotNull($adjust);
        $this->assertEquals('00', $adjust->deviceResponseCode);
    }

    public function testCardVerify()
    {
        $response = $this->device->creditVerify()
            ->withClerkId(1234)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testSaleReversal()
    {
        $response = $this->device->creditSale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $refundResponse = $this->device->creditReversal()
            ->withTerminalRefNumber($response->terminalRefNumber)
            ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }

    public function testVerifyWithTokenRequest()
    {
        $response = $this->device->creditVerify()
            ->withRequestMultiUseToken(1)
            ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
    }

    public function testPreAuth()
    {
        $response = $this->device->creditAuth(10)
                ->withAllowDuplicates(1)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $this->assertNotNull($response->transactionId);

        $captureResponse = $this->device->creditCapture(10)
                ->withTransactionId($response->transactionId)
                ->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
    }
    
    public function testHealthCareCardSale()
    {
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->realTimeSubstantiation = true;
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);

        $response = $this->device->creditSale(10)
                ->withAllowDuplicates(1)
                ->withAutoSubstantiation($autoSubAmounts)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testCreditTokenRequest()
    {
        $response = $this->device->creditToken()
        ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
        
        $saleResponse = $this->device->creditSale(10)
        ->withToken($response->token)
        ->execute();
        
        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
    }
    
    public function testCardVerifyTokenSale()
    {
        $response = $this->device->creditVerify()
        ->withRequestMultiUseToken(1)
        ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
        $this->assertNotNull($response->cardBrandTransId);
        
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->realTimeSubstantiation = true;
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);
        
        $saleResponse = $this->device->creditSale(100)
        ->withToken($response->token)
        ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
        ->withAutoSubstantiation($autoSubAmounts)
        ->execute();
        
        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
    }
}
