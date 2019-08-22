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

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());

        //open lane for credit transactions
        $this->device->openLane();
    }
    
    public function tearDown()
    {
        $this->waitAndReset();
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '10.138.141.20';
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

    public function testCreditSale()
    {
        $response = $this->device->creditSale(10)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testCreditAuth()
    {
        $response = $this->device->creditAuth(10)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testCreditCapture()
    {
        $authResponse = $this->device->creditAuth(15)
                ->execute();

        $this->assertNotNull($authResponse);
        $this->assertEquals('0', $authResponse->resultCode);
        $this->assertNotNull($authResponse->transactionId);
       
        $this->waitAndReset();

        $response = $this->device->creditCapture(15)
                ->withTransactionId($authResponse->transactionId)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCreditVoid()
    {
        $saleResponse = $this->device->creditSale(10)
                ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('0', $saleResponse->resultCode);
        $this->assertNotNull($saleResponse->transactionId);

        $this->waitAndReset();

        $response = $this->device->creditVoid()
                ->withTransactionId($saleResponse->transactionId)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCreditRefundByCard()
    {
        $response = $this->device->creditRefund(15)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCreditVerify()
    {
        $response = $this->device->creditVerify()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testSaleWithoutAmount()
    {
        $response = $this->device->creditSale()
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
        $response = $this->device->creditAuth()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testCaptureWithoutAmount()
    {
        $response = $this->device->creditCapture()
                ->withTransactionId(1234)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testRefundWithoutAmount()
    {
        $response = $this->device->creditRefund()
                ->withTransactionId(1234)
                ->execute();
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage transactionId cannot be null for this transaction type
     */
    public function testCaptureWithoutTransactionId()
    {
        $response = $this->device->creditCapture(10)
                ->execute();
    }
    
    public function testSaleStartCard()
    {
        $response = $this->device->startCard(PaymentMethodType::CREDIT);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $response = $this->device->creditSale(15)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testLostTransaction()
    {
        $requestIdProvider = new RequestIdProvider();
        $requestId = $requestIdProvider->getRequestId();
        
        $response = $this->device->creditSale(10)
                ->withRequestId($requestId)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->waitAndReset();
        
        $lostResponse = $this->device->creditSale(10)
                ->withRequestId($requestId)
                ->execute();
                
        $this->assertNotNull($lostResponse);
        $this->assertEquals('0', $lostResponse->resultCode);
        $this->assertEquals($requestId, $lostResponse->requestId);
        $this->assertEquals('1', $lostResponse->isStoredResponse);
    }
}
