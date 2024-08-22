<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaEBTTests extends TestCase
{

    private $device;

    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.8.181';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testEbtPurchase()
    {
        $response = $this->device->sale(10)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testEbtBalance()
    {
        $response = $this->device->balance()
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->availableBalance);
    }

    public function testEbtRefund()
    {
        $response = $this->device->refund(10)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
