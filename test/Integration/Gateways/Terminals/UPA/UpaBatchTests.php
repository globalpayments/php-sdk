<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\UPA\Responses\OpenTabDetailsResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\ArrayUtils;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaBatchTests extends TestCase
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

    public function testBatchClose()
    {
        $response = $this->device->endOfDay();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->batchId);
    }

    public function testGetOpenTabDetails()
    {
        $this->device->ecrId = '1';

        /** @var OpenTabDetailsResponse $response */
        $response = $this->device->getOpenTabDetails()->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);

        foreach ($response->openTabs as $transaction) {
            $this->assertNotNull($transaction->transactionId);
        }
    }

    public function testGetLastEOD()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->getLastEOD();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotEmpty($response->batchId);
    }
}
