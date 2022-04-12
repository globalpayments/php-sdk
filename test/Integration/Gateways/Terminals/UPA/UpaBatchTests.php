<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;

class UpaBatchTests extends TestCase
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

    public function testBatchClose()
    {
        $response = $this->device->eod();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->batchId);
        
        $response = $this->device->batchReport($response->batchId);
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->batchSummary);
        $this->assertNotNull($response->batchTransactions);
    }
    
    public function testGetOpenTabDetails()
    {
        $response = $this->device->getOpenTabDetails();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->reportRecords);
        
        foreach ($response->reportRecords as $transaction) {
            $captureResponse = $this->device->creditCapture($transaction['authorizedAmount'])
            ->withTransactionId($transaction['referenceNumber'])
            ->execute();
            
            $this->assertNotNull($captureResponse);
            $this->assertEquals('00', $captureResponse->deviceResponseCode);
            $this->assertNotNull($captureResponse->transactionId);
        }
    }
}
