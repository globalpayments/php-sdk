<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DataResidency;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
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
        
        $reflection = new \ReflectionClass($config);
        $properties = $reflection->getProperties();
        
        $hasDataResidency = false;
        foreach ($properties as $property) {
            if ($property->getName() === 'dataResidency') {
                $hasDataResidency = true;
                break;
            }
        }
        
        $this->assertFalse($hasDataResidency, 'PorticoConfig should not have dataResidency property');
    }
}

