<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\UPA\Entities\LineItem;
use GlobalPayments\Api\Terminals\UPA\Entities\CancelParameters;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;

class UpaAdminTests extends TestCase
{

    private $device;

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());
    }
    
    public function tearDown() : void
    {
        sleep(3);
    }

    protected function getConfig() : ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.210.79';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_SATURN_1000;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }
    
    public function testCancel()
    {
        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $response = $this->device->cancel($cancelParams);
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    public function testReboot()
    {
        $response = $this->device->reboot();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        sleep(60);
    }

    public function testLineItem()
    {
        $response = $this->device->lineItem("Line Item #1", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $response = $this->device->lineItem("Line Item #2", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $response = $this->device->lineItem("Line Item #3", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $this->device->cancel($cancelParams);
    }
}
