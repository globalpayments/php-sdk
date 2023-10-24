<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class UpaMicTests extends TestCase
{
    private IDeviceInterface $device;

    /**
     * @throws ApiException
     */
    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown(): void
    {
        sleep(3);
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function getConfig(): ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->deviceType = DeviceType::UPA_DEVICE;
        $config->connectionMode = ConnectionModes::MIC;
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::APP_ID; #gitleaks:allow
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::APP_KEY; #gitleaks:allow
        $gpApiConfig = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $gpApiConfig->country = 'US';
        $gpApiConfig->deviceCurrency = 'USD';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = "transaction_processing";
        $gpApiConfig->accessTokenInfo = $accessTokenInfo;
        $config->gatewayConfig = $gpApiConfig;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new LogManagement();

        return $config;
    }

    public function testCreditSale()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditSaleWithTerminalRefNumber()
    {
        $response = $this->device->sale(10)
            ->withTerminalRefNumber(GenerationUtils::getGuid())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testLineItem()
    {
        $response = $this->device->lineItem("Line Item #1", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditAuth()
    {
        $response = $this->device->authorize(10)
            ->withEcrId(13)
            ->withTerminalRefNumber('1234')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditAuthAndCapture()
    {
        $response = $this->device->authorize(10)
            ->withEcrId(13)
            ->withTerminalRefNumber('1234')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);

        $response = $this->device->capture(10)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditCapture_RandomId()
    {
        $response = $this->device->capture(10)
            ->withTransactionId(GenerationUtils::getGuid())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditRefund()
    {
        $response = $this->device->refund(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditVerify()
    {
        $response = $this->device->verify()
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditVoid()
    {
        $response = $this->device->void()
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditSale_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->device->sale()
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditAuth_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->device->authorize()
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditCapture_WithoutTransactionId()
    {
        $exceptionCaught = false;
        try {
            $this->device->capture(10)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('transactionId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditRefund_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->device->refund()
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}