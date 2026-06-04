<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiVoidApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-void-', $country);
    }

    private function assertVoid(
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

        $voided = $auth->reverse()->execute($serviceName);

        $this->assertNotNull($voided, 'Void response must not be null');
        $this->assertEquals('SUCCESS', $voided->responseCode, 'Void response code must be SUCCESS');
        $this->assertNotEmpty($voided->transactionId, 'Void transaction ID must be present');
    }

    public function voidScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider voidScenarioProvider
     */
    public function testVoidAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertVoid($card, 10.00, $currency, $service);
    }
}
