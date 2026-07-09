<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\{ManualEntryMethod, TransactionStatus};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiMultiCurrencyTest extends GpApiApacBaseTest
{
    private const EXPONENT_0 = ['ISK', 'KRW', 'VND'];

    private const EXPONENT_2 = ['AED', 'AUD', 'BDT', 'BND'];

    private const EXPONENT_3 = ['BHD', 'KWD', 'OMR'];

    private const BRANDS = ['VISA', 'MC'];

    private string $serviceName;

    protected function setUp(): void
    {
        $this->serviceName = $this->configureServiceForCountry('apac-multi-', 'SG');
    }

    public function allCurrencyScenarios(): array
    {
        return $this->buildCurrencyScenarios(self::EXPONENT_0)
            + $this->buildCurrencyScenarios(self::EXPONENT_2)
            + $this->buildCurrencyScenarios(self::EXPONENT_3);
    }

    private function buildCurrencyScenarios(array $currencies): array
    {
        $scenarios = [];

        foreach (self::BRANDS as $brand) {
            foreach ($currencies as $currency) {
                $scenarios["{$brand} {$currency}"] = [$brand, $currency];
            }
        }

        return $scenarios;
    }

    private function makeCard(string $brand, bool $moto = false): CreditCardData
    {
        $card = $this->createCard($brand);
        if ($moto) {
            $card->entryMethod = ManualEntryMethod::MOTO;
        }

        return $card;
    }

    private function currencyDelta(string $currency): float
    {
        if (in_array($currency, self::EXPONENT_0, true)) {
            return 0.0;
        }

        if (in_array($currency, self::EXPONENT_3, true)) {
            return 0.001;
        }

        return 0.01;
    }

    private function assertTransaction(object $response, string $expectedStatus): void
    {
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals($expectedStatus, $response->responseMessage);
        $this->assertNotEmpty($response->transactionId);
    }

    private function assertBalanceAmount(object $response, string $currency, float $expected = 2.00): void
    {
        $this->assertNotNull($response->balanceAmount, "Balance amount should not be null for {$currency}");
        $this->assertEqualsWithDelta(
            $expected,
            $response->balanceAmount,
            $this->currencyDelta($currency),
            "Balance amount mismatch for {$currency}"
        );
    }

    private function executeSale(string $brand, string $currency, bool $moto = false): object
    {
        return $this->makeCard($brand, $moto)
            ->charge(2.00)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($this->serviceName);
    }

    private function executeAuthorization(string $brand, string $currency, bool $moto = false): object
    {
        return $this->makeCard($brand, $moto)
            ->authorize(2.00)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($this->serviceName);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testSaleAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $sale = $this->executeSale($brand, $currency);

        $this->assertTransaction($sale, TransactionStatus::CAPTURED);
        $this->assertBalanceAmount($sale, $currency);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testAuthorizationAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $auth = $this->executeAuthorization($brand, $currency);

        $this->assertTransaction($auth, TransactionStatus::PREAUTHORIZED);
        $this->assertBalanceAmount($auth, $currency);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testPreAuthThenCaptureAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $auth = $this->executeAuthorization($brand, $currency);

        $this->assertTransaction($auth, TransactionStatus::PREAUTHORIZED);

        $capture = $auth->capture(2.00)
            ->withCurrency($currency)
            ->execute($this->serviceName);

        $this->assertTransaction($capture, TransactionStatus::CAPTURED);
        $this->assertBalanceAmount($capture, $currency);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testVoidAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $sale = $this->executeSale($brand, $currency);

        $this->assertTransaction($sale, TransactionStatus::CAPTURED);

        $void = $sale->reverse()->execute($this->serviceName);

        $this->assertNotNull($void);
        $this->assertEquals('SUCCESS', $void->responseCode);
        $this->assertNotEmpty($void->transactionId);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testAuthReversalAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $auth = $this->executeAuthorization($brand, $currency);

        $this->assertTransaction($auth, TransactionStatus::PREAUTHORIZED);

        $reversal = $auth->reverse()->execute($this->serviceName);

        $this->assertNotNull($reversal);
        $this->assertEquals('SUCCESS', $reversal->responseCode);
        $this->assertNotEmpty($reversal->transactionId);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testLinkedRefundAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $sale = $this->executeSale($brand, $currency);

        $this->assertTransaction($sale, TransactionStatus::CAPTURED);

        $refund = $sale->refund(2.00)
            ->withCurrency($currency)
            ->execute($this->serviceName);

        $this->assertTransaction($refund, TransactionStatus::CAPTURED);
        $this->assertBalanceAmount($refund, $currency);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testPartialCaptureAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $auth = $this->executeAuthorization($brand, $currency);

        $this->assertTransaction($auth, TransactionStatus::PREAUTHORIZED);

        $capture = $auth->capture(1.00)
            ->withCurrency($currency)
            ->execute($this->serviceName);

        $this->assertTransaction($capture, TransactionStatus::CAPTURED);
    }

    /** @dataProvider allCurrencyScenarios */
    public function testMotoSaleAcrossSupportedCurrencies(string $brand, string $currency): void
    {
        $sale = $this->executeSale($brand, $currency, true);

        $this->assertTransaction($sale, TransactionStatus::CAPTURED);
        $this->assertBalanceAmount($sale, $currency);
    }

    public function testSaleWithInvalidCurrencyThrowsGatewayException(): void
    {
        $this->expectException(GatewayException::class);

        $this->createCard('VISA')
            ->charge(2.00)
            ->withCurrency('XYZ')
            ->withAllowDuplicates(true)
            ->execute($this->serviceName);
    }
}