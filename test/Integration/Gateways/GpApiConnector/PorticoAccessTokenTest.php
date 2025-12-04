<?php

declare(strict_types=1);

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class PorticoAccessTokenTest extends TestCase
{
    private CreditCardData $masterCard;
    private CreditCardData $visaCard;

    private const PORTICO_DEVICE_ID = '11753';
    private const PORTICO_SITE_ID = '418948';
    private const PORTICO_LICENSE_ID = '388244';
    private const PORTICO_USERNAME = 'gateway1213846';
    private const PORTICO_PASSWORD = '$Test1234';
    private const SECRET_API_KEY = 'skapi_cert_MVISAgC05V8Amnxg2jARLKW-K4ONQeXejrWYCCA_Cw';
    private const APP_ID = 'jYtVGox8yvG6KQwlNHPxbfyDa13kwOGt';

    public function setUp(): void
    {
        ServicesContainer::removeConfiguration();

        $config = $this->createBaseConfig();
        $this->setPorticoCredentials($config);
        ServicesContainer::configureService($config, 'LegacyPorticoConfig');

        $config = $this->createBaseConfig();
        $config->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config, 'SecretApiKeyConfig');

        $config = $this->createBaseConfig();
        $this->setPorticoCredentials($config);
        $config->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config, 'FullPorticoConfig');

        $config = $this->createBaseConfig();
        $config->appId = self::APP_ID;
        $this->setPorticoCredentials($config);
        ServicesContainer::configureService($config, 'LegacyPorticoAppIdConfig');

        $config = $this->createBaseConfig();
        $config->appId = self::APP_ID;
        $config->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config, 'SecretApiKeyAppIdConfig');

        $config = $this->createBaseConfig();
        $config->appId = self::APP_ID;
        $this->setPorticoCredentials($config);
        $config->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config, 'FullPorticoAppIdConfig');

        $gpApiConfig = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        ServicesContainer::configureService($gpApiConfig);

        $this->initializeCards();
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testCreditSale_WithLegacyPortico(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('LegacyPorticoConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testSecretApiKeyAuth(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('SecretApiKeyConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testFullPorticoAuth(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('FullPorticoConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testPorticoWithAppId(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('LegacyPorticoAppIdConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testSecretKeyWithAppId(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('SecretApiKeyAppIdConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testFullPorticoWithAppId(): void
    {
        $response = $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('FullPorticoAppIdConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testCreditSale_WithGpApiCredentials(): void
    {
        $response = $this->visaCard->charge(12)
            ->withCurrency('USD')
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testInvalidPorticoCredentialsFails(): void
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);

        $config = $this->createBaseConfig();
        $config->deviceId = '99999';
        $config->siteId = '999999';
        $config->licenseId = '999999';
        $config->username = 'invalid_user';
        $config->password = 'invalid_pass';
        ServicesContainer::configureService($config, 'InvalidPorticoConfig');

        $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('InvalidPorticoConfig');
    }

    public function testInvalidSecretKeyFails(): void
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);

        $config = $this->createBaseConfig();
        $config->secretApiKey = 'skapi_cert_INVALID_KEY_12345';
        ServicesContainer::configureService($config, 'InvalidSecretKeyConfig');

        $this->masterCard->charge(12)
            ->withCurrency('USD')
            ->execute('InvalidSecretKeyConfig');
    }

    public function testPartialPorticoCredentialsFails(): void
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\ConfigurationException::class);

        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        $config->deviceId = self::PORTICO_DEVICE_ID;
        $config->siteId = self::PORTICO_SITE_ID;
        
        $config->validate();
    }

    public function testNoCredentialsFails(): void
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\ConfigurationException::class);

        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        
        $config->validate();
    }

    public function testGpApiStillWorks(): void
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        ServicesContainer::configureService($config, 'StandardGpApiConfig');

        $response = $this->visaCard->charge(15.50)
            ->withCurrency('USD')
            ->execute('StandardGpApiConfig');

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testPorticoWithGpApiTransactions(): void
    {
        $porticoConfig = $this->createBaseConfig();
        $this->setPorticoCredentials($porticoConfig);
        ServicesContainer::configureService($porticoConfig, 'PorticoAuthConfig');

        $response = $this->masterCard->charge(20.00)
            ->withCurrency('USD')
            ->execute('PorticoAuthConfig');

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->transactionId);
        $this->assertNotEmpty($response->transactionId);
    }

    public function testMultipleConfigsCoexist(): void
    {
        $config1 = $this->createBaseConfig();
        $this->setPorticoCredentials($config1);
        ServicesContainer::configureService($config1, 'MultiConfig1');

        $config2 = $this->createBaseConfig();
        $config2->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config2, 'MultiConfig2');

        $config3 = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        ServicesContainer::configureService($config3, 'MultiConfig3');

        $response1 = $this->masterCard->charge(10)->withCurrency('USD')->execute('MultiConfig1');
        $this->assertEquals('SUCCESS', $response1->responseCode);

        $response2 = $this->masterCard->charge(11)->withCurrency('USD')->execute('MultiConfig2');
        $this->assertEquals('SUCCESS', $response2->responseCode);

        $response3 = $this->visaCard->charge(12)->withCurrency('USD')->execute('MultiConfig3');
        $this->assertEquals('SUCCESS', $response3->responseCode);
    }

    public function testPorticoSupportsRefunds(): void
    {
        $config = $this->createBaseConfig();
        $this->setPorticoCredentials($config);
        ServicesContainer::configureService($config, 'RefundTestConfig');

        $charge = $this->masterCard->charge(25.00)
            ->withCurrency('USD')
            ->execute('RefundTestConfig');

        $this->assertEquals('SUCCESS', $charge->responseCode);

        $refund = $charge->refund()
            ->withCurrency('USD')
            ->execute('RefundTestConfig');

        $this->assertEquals('SUCCESS', $refund->responseCode);
    }

    public function testSecretKeySupportsVerify(): void
    {
        $config = $this->createBaseConfig();
        $config->secretApiKey = self::SECRET_API_KEY;
        ServicesContainer::configureService($config, 'VerifyTestConfig');

        $response = $this->masterCard->verify()
            ->withCurrency('USD')
            ->execute('VerifyTestConfig');

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testConfigWithAccessTokenInfo(): void
    {
        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = 'existing_token_12345';

        $config->validate();
        $this->assertTrue(true);
    }

    public function testConfigWithGpApiCredentials(): void
    {
        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        $config->appId = 'test_app_id';
        $config->appKey = 'test_app_key';

        $config->validate();
        $this->assertTrue(true);
    }

    public function testConfigWithPorticoCredentials(): void
    {
        $config = $this->createBaseConfig();
        $this->setPorticoCredentials($config);

        $config->validate();
        $this->assertTrue(true);
    }

    public function testConfigWithSecretApiKey(): void
    {
        $config = $this->createBaseConfig();
        $config->secretApiKey = self::SECRET_API_KEY;

        $config->validate();
        $this->assertTrue(true);
    }

    private function createBaseConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        $config->serviceUrl = 'https://apis-qa.globalpay.com/ucp';
        $config->country = 'US';
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->transactionProcessingAccountName = 'accessTokenValidationsecretKey';
        
        return $config;
    }

    private function setPorticoCredentials(GpApiConfig $config): void
    {
        $config->deviceId = self::PORTICO_DEVICE_ID;
        $config->siteId = self::PORTICO_SITE_ID;
        $config->licenseId = self::PORTICO_LICENSE_ID;
        $config->username = self::PORTICO_USERNAME;
        $config->password = self::PORTICO_PASSWORD;
    }

    private function initializeCards(): void
    {
        $this->masterCard = new CreditCardData();
        $this->masterCard->number = '5546259023665054';
        $this->masterCard->expMonth = '05';
        $this->masterCard->expYear = '2025';
        $this->masterCard->cvn = '123';
        $this->masterCard->cardPresent = false;

        $this->visaCard = new CreditCardData();
        $this->visaCard->number = '4263970000005262';
        $this->visaCard->expMonth = '12';
        $this->visaCard->expYear = date('Y', strtotime('+1 year'));
        $this->visaCard->cvn = '123';
        $this->visaCard->cardPresent = true;
    }

    private function assertTransactionResponse(Transaction $transaction, string $transactionStatus): void
    {
        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
    }
}