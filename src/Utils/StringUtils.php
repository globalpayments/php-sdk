<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class StringUtils
{
    private const DEFAULT_CURRENCY_DECIMALS = 2;

    private const CURRENCY_DECIMALS = [
        // 0 decimals — whole units
        'VND' => 0,
        'ISK' => 0,
        'KRW' => 0,

        // 3 decimals — milli-units
        'BHD' => 3,
        'KWD' => 3,
        'OMR' => 3,

        // 2 decimals — standard two-decimal
        // JPY: ISO 4217 decimals 0, but GP-API wire requires ×100
        'JPY' => 2,
        'AED' => 2,
        'AUD' => 2,
        'BDT' => 2,
        'BND' => 2,
        'BRL' => 2,
        'CAD' => 2,
        'CHF' => 2,
        'CLP' => 2,
        'CNY' => 2,
        'DKK' => 2,
        'EGP' => 2,
        'EUR' => 2,
        'GBP' => 2,
        'HKD' => 2,
        'IDR' => 2,
        'ILS' => 2,
        'INR' => 2,
        'LKR' => 2,
        'MOP' => 2,
        'MUR' => 2,
        'MVR' => 2,
        'MXN' => 2,
        'MYR' => 2,
        'NOK' => 2,
        'NZD' => 2,
        'PGK' => 2,
        'PHP' => 2,
        'PKR' => 2,
        'QAR' => 2,
        'RUB' => 2,
        'SAR' => 2,
        'SEK' => 2,
        'SGD' => 2,
        'THB' => 2,
        'TRY' => 2,
        'TWD' => 2,
        'USD' => 2,
        'VEF' => 2,
        'ZAR' => 2,
    ];

    public static function asPaddedAtEndString(?string $inString, int $toLength, string $padChar): string
    {
        $padStr = "";
        if (empty($inString)) {
            return "";
        }
        if ($toLength === strlen($inString)) {
            return $inString;
        }
        if ($toLength < strlen($inString)) {
            return substr($inString, 0, $toLength);
        }
        $padStr = str_repeat($padChar, $toLength - strlen($inString));
        return $inString . $padStr;
    }

    public static function asPaddedAtFrontString(?string $inString, int $toLength, string $padChar): string
    {
        $padStr = "";
        if (empty($inString)) {
            return "";
        }
        if ($toLength === strlen($inString)) {
            return $inString;
        }
        if ($toLength < strlen($inString)) {
            return substr($inString, 0, $toLength);
        }
        $padStr = str_repeat($padChar, $toLength - strlen($inString));
        return $padStr . $inString;
    }

    public static function toNumeric(string|int|float|null $value, ?string $currency = null): string
    {
        if (is_null($value)) {
            return "";
        }

        $decimals = self::getCurrencyDecimals($currency);

        if (is_float($value)) {
            $numeric = self::normalizeFloatNumericString($value);
        } else {
            $numeric = trim((string) $value);
        }

        if ($numeric !== '' && $numeric[0] === '+') {
            $numeric = substr($numeric, 1);
        }

        if ($numeric === '' || !is_numeric($numeric)) {
            throw new ArgumentException("A non well formed numeric value encountered!");
        }

        if (preg_match('/^-?(?:\d+|\d*\.\d+)[eE][+-]?\d+$/', $numeric)) {
            $numeric = self::normalizeFloatNumericString((float) $numeric);
        }

        if (!preg_match('/^-?(?:\d+|\d*\.\d+)$/', $numeric)) {
            throw new ArgumentException("A non well formed numeric value encountered!");
        }

        return self::toMinorUnitsString($numeric, $decimals);
    }

    private static function normalizeFloatNumericString(float $value): string
    {
        // Keep float inputs in fixed-point form to avoid scientific notation (e.g. 1.0E-7).
        $numeric = rtrim(rtrim(sprintf('%.15F', $value), '0'), '.');

        if ($numeric === '' || $numeric === '-0') {
            return '0';
        }

        return $numeric;
    }

    private static function toMinorUnitsString(string $numeric, int $decimals): string
    {
        $isNegative = false;
        if (strpos($numeric, '-') === 0) {
            $isNegative = true;
            $numeric = substr($numeric, 1);
        }

        $parts = explode('.', $numeric, 2);
        $whole = $parts[0] ?? '0';
        $fraction = $parts[1] ?? '';

        $roundDigitIndex = $decimals;
        $paddedFraction = str_pad($fraction, $roundDigitIndex + 1, '0', STR_PAD_RIGHT);
        $keptFraction = $decimals > 0 ? substr($paddedFraction, 0, $decimals) : '';
        $roundDigit = (int) $paddedFraction[$roundDigitIndex];

        $minorUnits = ltrim($whole . $keptFraction, '0');
        if ($minorUnits === '') {
            $minorUnits = '0';
        }

        if ($roundDigit >= 5) {
            $minorUnits = self::incrementNumericString($minorUnits);
        }

        if ($isNegative && $minorUnits !== '0') {
            return '-' . $minorUnits;
        }

        return $minorUnits;
    }

    private static function incrementNumericString(string $value): string
    {
        $digits = str_split($value === '' ? '0' : $value);
        $carry = 1;

        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $sum = ((int) $digits[$i]) + $carry;
            $digits[$i] = (string) ($sum % 10);
            $carry = (int) floor($sum / 10);
            if ($carry === 0) {
                break;
            }
        }

        if ($carry > 0) {
            array_unshift($digits, (string) $carry);
        }

        return implode('', $digits);
    }

    /**
     * @param string|null $str
     * @param string|null $currency
     *
     * @return float|int
     */
    public static function toAmount(?string $str, ?string $currency = null): float|int
    {
        if (is_null($str)) {
            return 0;
        }

        $numeric = trim($str);
        if ($numeric === '') {
            return 0;
        }

        if (!is_numeric($numeric)) {
            throw new ArgumentException("A non well formed numeric value encountered!");
        }

        // Normalize scientific notation to fixed-point before decimal handling (e.g. "1.0E+3").
        if (preg_match('/^-?(?:\d+|\d*\.\d+)[eE][+-]?\d+$/', $numeric)) {
            $numeric = self::normalizeFloatNumericString((float) $numeric);
        }

        $decimals = self::getCurrencyDecimals($currency);
        if ($decimals === 0) {
            if (!preg_match('/^-?\d+$/', $numeric)) {
                throw new ArgumentException("A non well formed numeric value encountered!");
            }
            return (int) $numeric;
        }

        return ((float) $numeric) / (10 ** $decimals);
    }

    private static function getCurrencyDecimals(?string $currency): int
    {
        if (empty($currency)) {
            return self::DEFAULT_CURRENCY_DECIMALS;
        }

        $normalizedCurrency = strtoupper($currency);
        return self::CURRENCY_DECIMALS[$normalizedCurrency] ?? self::DEFAULT_CURRENCY_DECIMALS;
    }

    /**
     * Strip all non-numeric characters
     *
     * @param string $value
     *
     * @return mixed
     */
    public static function validateToNumber(?string $value): ?string
    {
        return preg_replace("/[^0-9]/", "", $value);
    }

    /**
     * @param string $hexString
     * @return mixed
     */
    public static function bytesFromHex(?string $hexString): ?string
    {
        return pack("H*" , strtolower($hexString));
    }

    public static function isJson($string) : bool
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function boolToString($value): ?string
    {
        if (!is_bool($value)) {
            return null;
        }

        return json_encode($value);
    }

    /**
     * Convert a boolean, integer, or string value to "YES" or "NO".
     * Used across GpAPI flows (HPP, hosted payment pages, and other boolean flag serialization).
     * 
     * @param mixed $value The value to convert (bool, int 0/1, or string YES/NO in any case)
     * @return string|null Returns "YES" or "NO" on success, or null for unsupported types/values
     */
    public static function boolToYesNo(mixed $value) : ?string
    {
        // Handle boolean input
        if (is_bool($value)) {
            return $value ? 'YES' : 'NO';
        }
        
        // Handle integer input (0 or 1 only)
        if (is_int($value) && in_array($value, [0, 1], true)) {
            return $value === 1 ? 'YES' : 'NO';
        }
        
        // Handle string input (case-insensitive YES/NO)
        if (is_string($value)) {
            $normalized = strtoupper(trim($value));
            if ($normalized === 'YES') {
                return 'YES';
            } elseif ($normalized === 'NO') {
                return 'NO';
            }
            // Invalid string value
            return null;
        }
        
        // Unsupported type
        return null;
    }
}
