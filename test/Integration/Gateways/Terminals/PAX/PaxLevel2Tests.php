<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Entities\Enums\TaxType;

class PaxLevel2Tests extends TestCase
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

    // PoNumber
    public function testCheckPoNumber()
    {
        $response = $this->device->creditSale(10)
            ->withPoNumber("123456789")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    // CustomerCode
    public function testCheckCustomerCode()
    {
        $response = $this->device->creditSale(11)
            ->withCustomerCode("123456789")
            ->withTaxAmount(1.22)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    // TaxExempt
    public function testCheckTaxExcemptTrue()
    {
        $response = $this->device->creditSale(12)
            ->withCustomerCode("123456789")
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testCheckTaxExcemptFalse()
    {
        $response = $this->device->creditSale(13)
            ->withTaxAmount(1.22)
            ->withCustomerCode("987654321")
            ->withTaxType(TaxType::SALES_TAX)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    // TaxExemptId
    public function testCheckTaxExemptId()
    {
        $response = $this->device->creditSale(14)
            ->withCustomerCode("987654321")
            ->withTaxType(TaxType::TAX_EXEMPT, "987654321")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    // All fields
    public function testcheckAllFields()
    {
        $response = $this->device->creditSale(15)
            ->withPoNumber("123456789")
            ->withCustomerCode("8675309")
            ->withTaxType(TaxType::TAX_EXEMPT, "987654321")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
