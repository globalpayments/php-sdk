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
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
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
        $config->connectionMode = ConnectionModes::MEET_IN_THE_CLOUD;
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::MITC_UPA_APP_ID; #gitleaks:allow
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::MITC_UPA_APP_KEY; #gitleaks:allow
        $gpApiConfig = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $gpApiConfig->country = 'CA';
        $gpApiConfig->deviceCurrency = 'CAD';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = "9187";
        $gpApiConfig->accessTokenInfo = $accessTokenInfo;
        $config->gatewayConfig = $gpApiConfig;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testCreditSale()
    {
        $response = $this->device->sale(10)
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditSaleWithTerminalRefNumber()
    {
        $response = $this->device->sale(10)
            ->withTerminalRefNumber(GenerationUtils::getGuid())
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testLineItem()
    {
        $this->device->ecrId = '12';
        $response = $this->device->lineItem("Line Item #1", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditAuth()
    {
        $response = $this->device->authorize(10)
            ->withEcrId("10")
            ->withTerminalRefNumber('1234')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditAuthAndCapture()
    {
        $response = $this->device->authorize(10)
            ->withEcrId("13")
            ->withTerminalRefNumber('1234')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);

        $response = $this->device->capture(10)
            ->withEcrId("12")
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditCapture_RandomId()
    {
        $response = $this->device->capture(10)
            ->withEcrId('13')
            ->withTransactionId(GenerationUtils::getGuid())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditRefund()
    {
        $response = $this->device->refund(10)
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditVerify()
    {
        $response = $this->device->verify()
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }

    public function testCreditVoid()
    {
        $response = $this->device->sale(10)
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
        $this->assertNotNull($response->transactionId);

        $response = $this->device->void()
            ->withEcrId('13')
            ->withTransactionId($response->transactionId)
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
                ->withEcrId('13')
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
                ->withEcrId('13')
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
                ->withEcrId('13')
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
                ->withEcrId('13')
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEndOfDay()
    {
        $this->device->ecrId = '13';
        $response = $this->device->endOfDay();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('INITIATED', $response->deviceResponseText);
    }
}