<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;

class HpaGiftTests extends TestCase
{

    private $device;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());

        //open lane for EBT transactions
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
        $config->timeout = 180;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function waitAndReset()
    {
        sleep(3);
        $this->device->reset();
    }

    public function testGiftSale()
    {
        $response = $this->device->giftSale(100)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testLoyaltySale()
    {
        $response = $this->device->giftSale(100)
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testGiftAddValue()
    {
        $response = $this->device->giftAddValue(100)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testLoyaltyAddValue()
    {
        $response = $this->device->giftAddValue(100)
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testGiftVoid()
    {
        $responseSale = $this->device->giftSale(100)
                ->execute();

        $this->assertNotNull($responseSale);
        $this->assertEquals('0', $responseSale->resultCode);
        
        $this->waitAndReset();
        
        $response = $this->device->giftVoid()
                ->withTransactionId($responseSale->transactionId)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testGiftBalance()
    {
        $response = $this->device->giftBalance()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testLoyaltyBalance()
    {
        $response = $this->device->giftBalance()
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testAddValueWithoutAmount()
    {
        $this->device->giftAddValue()
                ->execute();
    }
    
    public function testSaleStartCard()
    {
        $response = $this->device->startCard(PaymentMethodType::GIFT);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $response = $this->device->giftSale(100)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->transactionId);
    }
}
