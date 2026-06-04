<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiAuthApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-auth-', $country);
    }

    private function assertAuth(
        CreditCardData $card,
        float $amount,
        string $currency,
        string $serviceName
    ): void {
        $response = $card->authorize($amount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        $this->assertNotNull($response, 'Response must not be null');
        $this->assertNotEmpty($response->transactionId, 'Transaction ID must be present');
        $this->assertEquals('SUCCESS', $response->responseCode, 'Result code must be SUCCESS');
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage, 'Status must be PREAUTHORIZED for auth');
        $this->assertNotEmpty($response->authorizationCode, 'Authorization code must be present');
    }

    public function authScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
    * @dataProvider authScenarioProvider
     */
    public function testAuthConnectivityAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertAuth($card, 10.00, $currency, $service);
    }
}
