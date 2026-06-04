<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiRefundApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-refund-', $country);
    }

    private function assertRefund(
        CreditCardData $card,
        float $saleAmount,
        float $refundAmount,
        string $currency,
        string $serviceName
    ): void {
        $sale = $card->charge($saleAmount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        $this->assertSuccessfulTransaction($sale, TransactionStatus::CAPTURED, 'Sale');

        $refund = $sale->refund($refundAmount)
            ->withCurrency($currency)
            ->execute($serviceName);

        $this->assertSuccessfulTransaction($refund, TransactionStatus::CAPTURED, 'Refund');
    }

    private function assertSuccessfulTransaction(object $transaction, string $expectedStatus, string $context): void
    {
        $this->assertNotNull($transaction, $context . ' response must not be null');
        $this->assertEquals('SUCCESS', $transaction->responseCode, $context . ' response code must be SUCCESS');
        $this->assertEquals($expectedStatus, $transaction->responseMessage, $context . ' status must match expected status');
        $this->assertNotEmpty($transaction->transactionId, $context . ' transaction ID must be present');
    }

    public function refundScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider refundScenarioProvider
     */
    public function testRefundAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertRefund($card, 12.00, 1.00, $currency, $service);
    }
}
