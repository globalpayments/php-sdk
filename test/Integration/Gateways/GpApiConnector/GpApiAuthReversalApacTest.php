<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiAuthReversalApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-auth-reversal-', $country);
    }

    private function assertAuthReversal(
        CreditCardData $card,
        float $authAmount,
        string $currency,
        string $serviceName
    ): void {
        $auth = $card->authorize($authAmount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        $this->assertEquals('SUCCESS', $auth->responseCode, 'Auth response code must be SUCCESS');
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $auth->responseMessage, 'Auth status must be PREAUTHORIZED');
        $this->assertNotEmpty($auth->transactionId, 'Auth transaction ID must be present');

        $reversal = $auth->reverse()->execute($serviceName);

        $this->assertNotNull($reversal, 'Reversal response must not be null');
        $this->assertEquals('SUCCESS', $reversal->responseCode, 'Reversal response code must be SUCCESS');
        $this->assertNotEmpty($reversal->transactionId, 'Reversal transaction ID must be present');
    }

    public function authReversalScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider authReversalScenarioProvider
     */
    public function testAuthReversalAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertAuthReversal($card, 10.00, $currency, $service);
    }
}
