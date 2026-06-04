<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiMotoApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-moto-', $country);
    }

    private function assertMotoTransaction(
        CreditCardData $card,
        float $amount,
        string $currency,
        string $serviceName
    ): void {
        $card->entryMethod = ManualEntryMethod::MOTO;

        $response = $card->charge($amount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        $this->assertNotNull($response, 'Response must not be null');
        $this->assertNotEmpty($response->transactionId, 'Transaction ID must be present');
        $this->assertEquals('SUCCESS', $response->responseCode, 'Response code must be SUCCESS');
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage, 'Status must be CAPTURED');
        $this->assertNotEmpty($response->authorizationCode, 'Authorization code must be present');
    }

    public function motoScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider motoScenarioProvider
     */
    public function testMotoTransactionsAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertMotoTransaction($card, 1.00, $currency, $service);
    }
}
