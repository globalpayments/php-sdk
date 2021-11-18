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
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;

class UpaAdminTests extends TestCase
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
        $config->ipAddress = '192.168.210.79';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_SATURN_1000;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new LogManagement();

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
        $lineItemDetails = [];
        
        $lineItem1 = new LineItem();
        $lineItem1->lineItemLeft = "Line Item #1";
        $lineItem1->lineItemRight = "10.00";
        $lineItemDetails[] = $lineItem1;
        
        $lineItem2 = new LineItem();
        $lineItem2->lineItemLeft = "Line Item #2";
        $lineItem2->lineItemRight = "10.00";
        $lineItemDetails[] = $lineItem2;
        
        $lineItem3 = new LineItem();
        $lineItem3->lineItemLeft = "Line Item #3";
        $lineItem3->lineItemRight = "10.00";
        $lineItemDetails[] = $lineItem3;
        
        $response = $this->device->lineItem($lineItemDetails);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $this->device->cancel($cancelParams);
    }
}
