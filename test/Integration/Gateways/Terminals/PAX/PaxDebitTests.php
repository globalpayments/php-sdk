<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
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

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown() : void
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
        $response = $this->device->sale(10)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
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
        $response = $this->device->sale()
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
    }

    public function testDebitRefund()
    {
        $response = $this->device->sale(10)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withAllowDuplicates(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        $this->assertNotNull($response->transactionId);

        $refundResponse = $this->device->refund(10)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('OK', $refundResponse->deviceResponseText);
    }
}
