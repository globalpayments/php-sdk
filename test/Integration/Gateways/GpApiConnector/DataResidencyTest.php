<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\{Channel, DataResidency, Environment, ServiceEndpoints};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\{GpApiConfig, PorticoConfig};
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use PHPUnit\Framework\TestCase;

class DataResidencyTest extends TestCase
{
    private CreditCardData $card;

    public function setUp(): void
    {
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = "05";
        $this->card->expYear = "2026";
        $this->card->cvn = "852";
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    /** Test DataResidency set to EU uses EU endpoint */
    public function testDataResidencyEU(): void
    {
        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::EU_APP_ID;
        $config->appKey = BaseGpApiTestConfig::EU_APP_KEY;
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardNotPresent;
        $config->dataResidency = DataResidency::EU;
        $config->requestLogger = new RequestConsoleLogger();

        ServicesContainer::configureService($config, 'eu-config');
        
        $this->assertEquals(ServiceEndpoints::GP_API_TEST_EU, $config->serviceUrl);

        $response = $this->card->charge(1.00)
            ->withCurrency('EUR')
            ->execute('eu-config');

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->transactionId);
    }

    /** Test DataResidency defaults to NONE when not set */
    public function testDataResidencyDefaultsToNone(): void
    {
        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::$appId;
        $config->appKey = BaseGpApiTestConfig::$appKey;
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardNotPresent;
        $config->requestLogger = new RequestConsoleLogger();

        ServicesContainer::configureService($config, 'default-config');
        
        $this->assertEquals(DataResidency::NONE, $config->dataResidency);
        $this->assertEquals(ServiceEndpoints::GP_API_TEST, $config->serviceUrl);

        $response = $this->card->charge(1.00)
            ->withCurrency('USD')
            ->execute('default-config');

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->transactionId);
    }

    /** Test Non-GPAPI config does not have DataResidency property */
    public function testNonGpApiNoDataResidency(): void
    {
        $config = new PorticoConfig();
        $this->assertObjectNotHasProperty('dataResidency', $config);
    }

    /** Test DataResidency EU + QA environment routes to QA EU endpoint */
    public function testDataResidencyEuQaRoutesToQaEuEndpoint(): void
    {
        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::EU_APP_ID;
        $config->appKey = BaseGpApiTestConfig::EU_APP_KEY;
        $config->environment = GpApiConfig::QA_ENVIRONMENT;
        $config->channel = Channel::CardNotPresent;
        $config->dataResidency = DataResidency::EU;
        $config->requestLogger = new RequestConsoleLogger();

        ServicesContainer::configureService($config, 'qa-eu-config');
        $this->assertEquals(ServiceEndpoints::GP_API_QA_EU, $config->serviceUrl);

        $this->expectException(GatewayException::class);
        $this->card->charge(1.00)
            ->withCurrency('USD')
            ->execute('qa-eu-config');
    }

    /** Test DataResidency EU + Production environment routes to Production EU endpoint */
    public function testDataResidencyEuProductionRoutesToProdEuEndpoint(): void
    {
        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::EU_APP_ID;
        $config->appKey = BaseGpApiTestConfig::EU_APP_KEY;
        $config->environment = Environment::PRODUCTION;
        $config->channel = Channel::CardNotPresent;
        $config->dataResidency = DataResidency::EU;
        $config->requestLogger = new RequestConsoleLogger();

        ServicesContainer::configureService($config, 'prod-eu-config');
        $this->assertEquals(ServiceEndpoints::GP_API_PRODUCTION_EU, $config->serviceUrl);

        $this->expectException(GatewayException::class);
        $this->card->charge(1.00)
            ->withCurrency('USD')
            ->execute('prod-eu-config');
    }

    /** Test manually provided serviceUrl overrides residency/environment routing */
    public function testManualServiceUrlOverridesRouting(): void
    {
        $customUrl = 'https://example.test/custom-gpapi-endpoint';

        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::EU_APP_ID;
        $config->appKey = BaseGpApiTestConfig::EU_APP_KEY;
        $config->environment = GpApiConfig::QA_ENVIRONMENT;
        $config->channel = Channel::CardNotPresent;
        $config->dataResidency = DataResidency::EU;
        $config->serviceUrl = $customUrl;

        ServicesContainer::configureService($config, 'manual-service-url-config');

        $this->assertEquals($customUrl, $config->serviceUrl);
    }
}

