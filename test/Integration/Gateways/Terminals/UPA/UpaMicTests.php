<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use _PHPStan_b8e553790\Nette\Schema\Elements\Base;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use PHPUnit\Framework\TestCase;

class UpaMicTests  extends TestCase
{
    private $device;

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown() : void
    {
        sleep(3);
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    protected function getConfig() : ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->deviceType = DeviceType::UPA_DEVICE;
        $config->connectionMode = ConnectionModes::MIC;
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::UPA_MIC_DEVICE_APP_ID;
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::UPA_MIC_DEVICE_APP_KEY;
        $gpApiConfig = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $gpApiConfig->environment = Environment::QA;
        $gpApiConfig->country = 'US';
        $gpApiConfig->deviceCurrency = 'USD';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = "DublinTransAccount_Nucleus01";
        $accessTokenInfo->transactionProcessingAccountID = 'TRA_4dca2d890e914f7da0790a70947b98c8';
        $gpApiConfig->accessTokenInfo = $accessTokenInfo;
        $config->gatewayConfig = $gpApiConfig;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new LogManagement();

        return $config;
    }

    public function testCreditSale()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->sale(10)
            ->withEcrId(13)
            ->withTerminalRefNumber('1234')
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
}