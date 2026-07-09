<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\CI\upa_mitc;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\{ConnectionConfig, TerminalResponse};
use GlobalPayments\Api\Terminals\Enums\{ConnectionModes, DeviceType};
use GlobalPayments\Api\Tests\Utils\CiTestingHarness;
use PHPUnit\Framework\TestCase;

final class UpaFunctionalityTest extends TestCase
{
    const MITC_UPA_APP_ID = '6l8Xr23kHL9tGmAtXUvCEXKskvF7aLGq';
    const MITC_UPA_APP_KEY = 'z0ApiLDfXrKmrlNa';
    const ECR_ID = '13';
    const CACHE_MODE = CiTestingHarness::LOCKED;

    private static CiTestingHarness $harness;

    public static function setUpBeforeClass(): void
    {
        self::$harness = new CiTestingHarness(
            'https://apis.sandbox.globalpay.com/ucp',
            self::CACHE_MODE,
            'UpaFunctionalityTest'
        );
    }

    private function getGpApiConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = self::MITC_UPA_APP_ID;
        $config->appKey = self::MITC_UPA_APP_KEY;
        $config->channel = Channel::CardPresent;
        $config->country = 'US';
        $config->deviceCurrency = 'USD';
        $config->enableLogging = true;
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = '90916726';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->serviceUrl = self::$harness->getTestingUrl();
        return $config;
    }

    private function createDevice(string $testKey)
    {
        $connectionConfig = new ConnectionConfig();
        $connectionConfig->deviceType = DeviceType::UPA_DEVICE;
        $connectionConfig->connectionMode = ConnectionModes::MEET_IN_THE_CLOUD;
        $connectionConfig->timeout = 30000;
        $connectionConfig->gatewayConfig = $this->getGpApiConfig();
        $connectionConfig->requestIdProvider = self::$harness->createRequestIdProvider($testKey);
        $device = DeviceService::create($connectionConfig);
        self::$harness->reset();
        return $device;
    }

    private function assertMitcUpaResponse(TerminalResponse $response): void
    {
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testCreditSale(): void
    {
        self::$harness->setFunction('UPA|Functionality|Credit Sale');

        $device = $this->createDevice('CreditSale');
        $amount = self::$harness->generateRandomDecimal('CreditSale_amount', 1, 10, 2);
        $response = $device->sale($amount)->withEcrId(self::ECR_ID)->execute();

        $this->assertNotNull($response);
        $this->assertMitcUpaResponse($response);
    }

    public function testRefundAgainstCard(): void
    {
        self::$harness->setFunction('UPA|Functionality|Refund against Card');

        $device = $this->createDevice('RefundAgainstCard');
        $amount = self::$harness->generateRandomDecimal('RefundAgainstCard_amount', 1, 10, 2);
        $response = $device->refund($amount)->withEcrId(self::ECR_ID)->execute();

        $this->assertNotNull($response);
        $this->assertMitcUpaResponse($response);
    }

    public function testRefundAgainstTransactionId(): void
    {
        self::$harness->setFunction('UPA|Functionality|Refund against Transaction ID');

        $device = $this->createDevice('RefundAgainstTransactionId');
        $amount = self::$harness->generateRandomDecimal('RefundAgainstTransactionId_amount', 1, 10, 2);
        $sale = $device->sale($amount)->withEcrId(self::ECR_ID)->execute();

        $this->assertNotNull($sale);
        $this->assertMitcUpaResponse($sale);

        if (self::CACHE_MODE === CiTestingHarness::UNLOCKED) {
            sleep(15);
        }

        $refund = $device->refund($amount)
            ->withEcrId(self::ECR_ID)
            ->withTransactionId($sale->transactionId)
            ->execute();

        $this->assertNotNull($refund);
        $this->assertMitcUpaResponse($refund);
    }
}
