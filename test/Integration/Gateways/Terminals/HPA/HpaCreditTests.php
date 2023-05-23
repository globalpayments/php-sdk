<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;

class HpaCreditTests extends TestCase
{

    private $device;

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());

        //open lane for credit transactions
        $this->device->openLane();
    }
    
    public function tearDown() : void
    {
        $this->waitAndReset();
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '10.138.141.14';
        $config->port = '12345';
        $config->deviceType = DeviceType::HPA_ISC250;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 180;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }
    
    public function waitAndReset()
    {
        sleep(3);
        $this->device->reset();
    }

    public function testSale()
    {
        $response = $this->device->sale(10)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testAuthorize()
    {
        $response = $this->device->authorize(10)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testCapture()
    {
        $authResponse = $this->device->authorize(15)
                ->execute();

        $this->assertNotNull($authResponse);
        $this->assertEquals('0', $authResponse->resultCode);
        $this->assertNotNull($authResponse->transactionId);
       
        $this->waitAndReset();

        $response = $this->device->capture(15)
                ->withTransactionId($authResponse->transactionId)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testVoid()
    {
        $saleResponse = $this->device->sale(10)
                ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('0', $saleResponse->resultCode);
        $this->assertNotNull($saleResponse->transactionId);

        $this->waitAndReset();

        $response = $this->device->void()
                ->withTransactionId($saleResponse->transactionId)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCreditRefundByCard()
    {
        $response = $this->device->refund(15)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testVerify()
    {
        $response = $this->device->verify()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testSaleWithoutAmount()
    {
        $this->expectException(GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("amount cannot be null for this transaction type");
        $response = $this->device->sale()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testAuthWithoutAmount()
    {
        $response = $this->device->authorize()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCaptureWithoutAmount()
    {
        $this->expectException(GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("amount cannot be null for this transaction type");
        $response = $this->device->capture()
                ->withTransactionId(1234)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testRefundWithoutAmount()
    {
        $this->expectException(GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("amount cannot be null for this transaction type");
        $response = $this->device->refund()
                ->withTransactionId(1234)
                ->execute();
    }

    public function testCaptureWithoutTransactionId()
    {
        $this->expectExceptionMessage("transactionId cannot be null for this transaction type");
        $this->expectException(GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $response = $this->device->capture(10)
                ->execute();
    }
    
    public function testSaleStartCard()
    {
        $response = $this->device->startCard(PaymentMethodType::CREDIT);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $response = $this->device->sale(15)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testLostTransaction()
    {
        $requestIdProvider = new RequestIdProvider();
        $requestId = $requestIdProvider->getRequestId();
        
        $response = $this->device->sale(10)
                ->withRequestId($requestId)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->waitAndReset();
        
        $lostResponse = $this->device->sale(10)
                ->withRequestId($requestId)
                ->execute();
                
        $this->assertNotNull($lostResponse);
        $this->assertEquals('0', $lostResponse->resultCode);
        $this->assertEquals($requestId, $lostResponse->requestId);
        $this->assertEquals('1', $lostResponse->isStoredResponse);
    }
}
