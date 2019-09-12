<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;

class HpaDebitTests extends TestCase
{

    private $device;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());

        //open lane for Debit transactions
        $this->device->openLane();
    }
    
    public function tearDown()
    {
        $this->waitAndReset();
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '10.138.141.7';
        $config->port = '12345';
        $config->deviceType = DeviceType::HPA_ISC250;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 60;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function waitAndReset()
    {
        sleep(3);
        $this->device->reset();
    }

    public function testDebitSale()
    {
        $response = $this->device->debitSale(10)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testDebitRefund()
    {
        $saleResponse = $this->device->debitSale(15)
                ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('0', $saleResponse->resultCode);
        $this->assertNotNull($saleResponse->transactionId);

        $this->waitAndReset();

        $response = $this->device->debitRefund(15)
                ->withTransactionId($saleResponse->transactionId)
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
        $response = $this->device->debitSale()
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
        $response = $this->device->debitRefund()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testSaleStartCard()
    {
        $response = $this->device->startCard(PaymentMethodType::DEBIT);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $response = $this->device->debitSale(15)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }
}
