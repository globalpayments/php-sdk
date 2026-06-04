<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use PHPUnit\Framework\TestCase;

abstract class GpApiApacBaseTest extends TestCase
{
    protected const ACCOUNT_NAME = 'transaction_processing';

    protected const SCENARIOS = [
        'HK Visa' => ['HK', 'HKD', 'VISA'],
        'HK Mastercard' => ['HK', 'HKD', 'MC'],
        'MO Visa' => ['MO', 'MOP', 'VISA'],
        'MO Mastercard' => ['MO', 'MOP', 'MC'],
        'PH Visa' => ['PH', 'PHP', 'VISA'],
        'PH Mastercard' => ['PH', 'PHP', 'MC'],
        'MY Visa' => ['MY', 'MYR', 'VISA'],
        'MY Mastercard' => ['MY', 'MYR', 'MC'],
        'SG Visa' => ['SG', 'SGD', 'VISA'],
        'SG Mastercard' => ['SG', 'SGD', 'MC'],
    ];

    protected const CARD_DETAILS = [
        'VISA' => ['number' => '4263970000005262', 'holder' => 'APAC VISA'],
        'MC' => ['number' => '5425230000004415', 'holder' => 'APAC MC'],
    ];

    protected function configureServiceForCountry(string $servicePrefix, string $country): string
    {
        $serviceName = $servicePrefix . strtolower($country);

        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::APAC_APP_ID;
        $config->appKey = BaseGpApiTestConfig::APAC_APP_KEY;
        $config->merchantId = null;
        $config->transactionAccountName = self::ACCOUNT_NAME;
        $config->channel = Channel::CardNotPresent;
        $config->country = $country;
        $config->environment = Environment::TEST;
        $config->serviceUrl = ServiceEndpoints::GP_API_TEST;
        $config->requestLogger = new RequestConsoleLogger();

        ServicesContainer::configureService($config, $serviceName);

        return $serviceName;
    }

    protected function createCard(string $brand): CreditCardData
    {
        $cardData = self::CARD_DETAILS[$brand] ?? self::CARD_DETAILS['MC'];

        $card = new CreditCardData();
        $card->expMonth = '12';
        $card->expYear = '2030';
        $card->cvn = '123';
        $card->number = $cardData['number'];
        $card->cardHolderName = $cardData['holder'];

        return $card;
    }

    protected function apacScenarioProvider(): array
    {
        return self::SCENARIOS;
    }
}
