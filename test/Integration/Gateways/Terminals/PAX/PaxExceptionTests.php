<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\Enums\{ConnectionModes, DeviceType};
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
 
class PaxExceptionTests extends TestCase {
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
        $config->ipAddress = '192.168.1.10';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testCreditSaleExpiredCard() 
    {
        $response = $this->device->sale(10.32)
            ->withAllowDuplicates(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('54', $response->responseCode);
    }

    public function testDebitSaleExpiredCard() 
    {
        $response = $this->device->sale(10.32)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withAllowDuplicates(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('54', $response->responseCode);
    }

}