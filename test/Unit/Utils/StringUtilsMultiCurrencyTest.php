<?php

namespace GlobalPayments\Api\Tests\Unit\Utils;

use GlobalPayments\Api\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsMultiCurrencyTest extends TestCase
{
    public function standardEncodingProvider(): array
    {
        return [
            'USD standard' => ['USD', 10.00, '1000'],
            'USD fractional rounds' => ['USD', 1235.876, '123588'],
            'USD exact cents' => ['USD', 1235.88, '123588'],
            'GBP standard' => ['GBP', 7.80, '780'],
            'JPY wire exponent 2' => ['JPY', 10, '1000'],
            'JPY fractional rounds' => ['JPY', 1235.876, '123588'],
            'null currency default' => [null, 10.00, '1000'],
            'string numeric input' => ['USD', '10.00', '1000'],
            'unknown currency default' => ['XYZ', 10.00, '1000'],
            'lowercase currency' => ['usd', 10.00, '1000'],
        ];
    }

    public function zeroDecimalEncodingProvider(): array
    {
        return [
            'KRW whole unit' => ['KRW', 1236, '1236'],
            'KRW rounded whole unit' => ['KRW', 1235.876, '1236'],
            'ISK whole unit' => ['ISK', 5000, '5000'],
            'VND rounded whole unit' => ['VND', 25000.4, '25000'],
        ];
    }

    public function threeDecimalEncodingProvider(): array
    {
        return [
            'BHD milli-unit' => ['BHD', 10.500, '10500'],
            'KWD fractional milli-unit' => ['KWD', 1235.876, '1235876'],
            'OMR exact three decimals' => ['OMR', 1.001, '1001'],
        ];
    }

    public function decodeProvider(): array
    {
        return [
            'USD decode' => ['USD', '1000', 10.00, 0.01],
            'GBP decode' => ['GBP', '780', 7.80, 0.01],
            'KRW decode' => ['KRW', '1236', 1236, 0.0],
            'BHD decode' => ['BHD', '10500', 10.500, 0.001],
            'JPY decode' => ['JPY', '1000', 10, 0.01],
            'null currency decode' => [null, '1000', 10.00, 0.01],
            'negative USD decode' => ['USD', '-1000', -10.00, 0.01],
        ];
    }

    public function roundTripProvider(): array
    {
        return [
            'SGD' => ['SGD', 10.00, 0.01],
            'HKD' => ['HKD', 10.00, 0.01],
            'MOP' => ['MOP', 10.00, 0.01],
            'PHP' => ['PHP', 10.00, 0.01],
            'MYR' => ['MYR', 10.00, 0.01],
            'USD' => ['USD', 10.00, 0.01],
            'EUR' => ['EUR', 10.00, 0.01],
            'GBP' => ['GBP', 10.00, 0.01],
            'AUD' => ['AUD', 10.00, 0.01],
            'CAD' => ['CAD', 10.00, 0.01],
            'CLP' => ['CLP', 5000.00, 0.01],
            'KRW' => ['KRW', 10000.0, 0.0],
            'ISK' => ['ISK', 1000.0, 0.0],
            'VND' => ['VND', 25000.0, 0.0],
            'BHD' => ['BHD', 10.000, 0.001],
            'KWD' => ['KWD', 10.000, 0.001],
            'OMR' => ['OMR', 10.000, 0.001],
            'JPY' => ['JPY', 1000.0, 0.01],
        ];
    }

    /** @dataProvider standardEncodingProvider */
    public function testStandardEncoding(?string $currency, $amount, string $expected): void
    {
        $this->assertEquals($expected, StringUtils::toNumeric($amount, $currency));
    }

    /** @dataProvider zeroDecimalEncodingProvider */
    public function testZeroDecimalEncoding(string $currency, float|int $amount, string $expected): void
    {
        $this->assertEquals($expected, StringUtils::toNumeric($amount, $currency));
    }

    /** @dataProvider threeDecimalEncodingProvider */
    public function testThreeDecimalEncoding(string $currency, float $amount, string $expected): void
    {
        $this->assertEquals($expected, StringUtils::toNumeric($amount, $currency));
    }

    public function testCaseInsensitiveCurrencyHandling(): void
    {
        $this->assertEquals('1000', StringUtils::toNumeric(10, 'jpy'));
        $this->assertEquals('1000', StringUtils::toNumeric(10.00, 'Usd'));
        $this->assertEquals('10500', StringUtils::toNumeric(10.500, 'bhd'));
    }

    public function testNegativeEncoding(): void
    {
        $this->assertEquals('-1000', StringUtils::toNumeric(-10.00, 'USD'));
        $this->assertEquals('-1236', StringUtils::toNumeric(-1235.876, 'KRW'));
    }

    public function testNegativeZeroEncodingReturnsZero(): void
    {
        $this->assertEquals('0', StringUtils::toNumeric(-0.00, 'USD'));
    }

    /** @dataProvider decodeProvider */
    public function testDecoding(?string $currency, ?string $amount, float|int $expected, float $delta): void
    {
        $this->assertEqualsWithDelta($expected, StringUtils::toAmount($amount, $currency), $delta);
    }

    public function testDecodeNullAndEmptyStringReturnZero(): void
    {
        $this->assertEquals(0, StringUtils::toAmount(null, 'USD'));
        $this->assertEquals(0, StringUtils::toAmount('', 'USD'));
    }

    /** @dataProvider roundTripProvider */
    public function testRoundTripAcrossCurrencies(string $currency, float $amount, float $delta): void
    {
        $encoded = StringUtils::toNumeric($amount, $currency);
        $decoded = StringUtils::toAmount($encoded, $currency);

        $this->assertEqualsWithDelta($amount, $decoded, $delta);
    }

    public function testSmallFractionalAmounts(): void
    {
        $this->assertEquals('50', StringUtils::toNumeric(0.50, 'USD'));
        $this->assertEquals('1', StringUtils::toNumeric(0.01, 'USD'));
        $this->assertEquals('0', StringUtils::toNumeric(0.001, 'USD'));
    }

    public function testLargeAmounts(): void
    {
        $this->assertEquals('1000000', StringUtils::toNumeric(10000.00, 'SGD'));
        $this->assertEquals(10000.00, StringUtils::toAmount('1000000', 'SGD'));
    }

    public function testZeroAmountAlwaysReturnsZero(): void
    {
        $this->assertEquals('0', StringUtils::toNumeric(0, 'USD'));
        $this->assertEquals('0', StringUtils::toNumeric(0, 'KRW'));
        $this->assertEquals('0', StringUtils::toNumeric(0, 'BHD'));
        $this->assertEquals(0, StringUtils::toAmount('0', 'USD'));
        $this->assertEquals(0, StringUtils::toAmount('0', 'KRW'));
        $this->assertEquals(0, StringUtils::toAmount('0', 'BHD'));
    }

    public function testLeadingZerosAreStripped(): void
    {
        $this->assertEquals('50', StringUtils::toNumeric(0.50, 'USD'));
        $this->assertEquals('1', StringUtils::toNumeric(0.01, 'USD'));
    }

    public function testToAmountScientificNotationDecodes(): void
    {
        // "1e3" in scientific notation should decode to 10.00 for USD (1000 / 100)
        $this->assertEqualsWithDelta(10.00, StringUtils::toAmount('1e3', 'USD'), 0.01);
        // "1E+4" should decode to 100.00 for USD
        $this->assertEqualsWithDelta(100.00, StringUtils::toAmount('1E+4', 'USD'), 0.01);
    }

    public function testToAmountExponent0RejectsNonInteger(): void
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\ArgumentException::class);
        StringUtils::toAmount('1236.5', 'KRW');
    }

    public function testToAmountExponent0ScientificNotation(): void
    {
        // "1e3" scientific notation should normalize and decode to 1000 for KRW
        $this->assertEquals(1000, StringUtils::toAmount('1e3', 'KRW'));
    }
}
