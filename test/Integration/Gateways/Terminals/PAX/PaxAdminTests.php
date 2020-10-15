<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\Enums\SafMode;

class PaxAdminTests extends TestCase
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
    
    public function testIntialize()
    {
        $response = $this->device->initialize();
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        $this->assertNotNull($response->serialNumber);
    }
    
    public function testReset()
    {
        $response = $this->device->reset();
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }
    
    public function testCancel()
    {
        $this->device->cancel();
    }
    
    public function testReboot()
    {
        $this->markTestSkipped('Reboot skipped');
        
        $response = $this->device->reboot();
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }
    
    public function testPromptForSignature()
    {
        $response = $this->device->promptForSignature();
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }
    
    public function testGetSignature()
    {
        $response = $this->device->getSignatureFile();
        
       
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        $this->assertNotNull($response->signatureData);
    }
    
    public function testSetSafMode()
    {
        $response = $this->device->setSafMode(SafMode::STAY_OFFLINE);
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }
}
