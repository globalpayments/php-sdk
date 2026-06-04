<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

/**
 * Integration tests validating GP-API Sale endpoint connectivity.
 *
 * Endpoint: POST https://apis.sandbox.globalpay.com/ucp/transactions
 * Transaction type: SALE  |  capture_mode: AUTO
 *
 * Each test method covers one APAC region with Visa and Mastercard,
 * asserting all key response fields to prove the correct endpoint was hit.
 */
class GpApiSaleApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-sale-', $country);
    }

    /**
     * Executes a sale and asserts all GP-API response fields that confirm
     * the /transactions POST endpoint was reached and processed correctly.
     */
    private function assertSale(
        CreditCardData $card,
        float $amount,
        string $currency,
        string $serviceName
    ): void {
        $response = $card->charge($amount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        // Connectivity: token + endpoint reachable
        $this->assertNotNull($response, 'Response must not be null');
        $this->assertNotEmpty($response->transactionId, 'Transaction ID must be present (endpoint was hit)');

        // Top-level result
        $this->assertEquals('SUCCESS', $response->responseCode, 'Result code must be SUCCESS');
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage, 'Status must be CAPTURED for a sale');

        // Authorization code proves card-scheme processing happened
        $this->assertNotEmpty($response->authorizationCode, 'Authorization code must be present');
    }

    public function saleScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider saleScenarioProvider
     */
    public function testSaleConnectivityAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);
        $this->assertSale($card, 10.00, $currency, $service);
    }
}
