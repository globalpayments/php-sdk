<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\Enums\SafUpload;
use GlobalPayments\Api\Terminals\Enums\SafDelete;
use GlobalPayments\Api\Terminals\Enums\SafReportSummary;

class PaxBatchTests extends TestCase
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
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function testBatchClose()
    {
        $response = $this->device->batchClose();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testSafUpload()
    {
        $response = $this->device->safUpload(SafUpload::ALL_TRANSACTION);

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testSafDelete()
    {
        $response = $this->device->safDelete(SafDelete::DELETE_ALL_SAF_RECORD);

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testSafReport()
    {
        $response = $this->device->safSummaryReport(SafReportSummary::ALL_REPORT);

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
