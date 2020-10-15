<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;

class PaxDebitTests extends TestCase
{

    private $device;
    protected $address;

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
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function testDebitSale()
    {
        $response = $this->device->debitSale(10)
                ->withAllowDuplicates(1)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testSaleNoAmount()
    {
        $response = $this->device->debitSale()
                ->execute();
    }

    public function testDebitRefund()
    {
        $response = $this->device->debitSale(10)
                ->withAllowDuplicates(1)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        $this->assertNotNull($response->transactionId);

        $refundResponse = $this->device->debitRefund(10)
                ->withTransactionId($response->transactionId)
                ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('OK', $refundResponse->deviceResponseText);
    }
}
