<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;

class HpaEbtTests extends TestCase
{

    private $device;

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());

        //open lane for EBT transactions
        $this->device->openLane();
    }
    
    public function tearDown() : void
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
        $config->timeout = 180;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function waitAndReset()
    {
        sleep(3);
        $this->device->reset();
    }

    public function testEbtBalance()
    {
        $response = $this->device->balance()
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testEbtPurchase()
    {
        $saleResponse = $this->device->sale(10)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();
        
        $this->assertNotNull($saleResponse);
        $this->assertEquals('0', $saleResponse->resultCode);
        $this->assertNotNull($saleResponse->transactionId);
    }

    public function testEbtRefund()
    {
        $saleResponse = $this->device->sale(15)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('0', $saleResponse->resultCode);
        $this->assertNotNull($saleResponse->transactionId);

        $this->waitAndReset();

        $response = $this->device->refund(15)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->withTransactionId($saleResponse->transactionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testSaleStartCard()
    {
        $response = $this->device->startCard(PaymentMethodType::EBT);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $response = $this->device->sale(15)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }
}
