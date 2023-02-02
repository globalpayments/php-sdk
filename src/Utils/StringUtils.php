<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use Locale;
use NumberFormatter;

class StringUtils
{

    public static function asPaddedAtEndString($inString, $toLength, $padChar)
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

    public static function asPaddedAtFrontString($inString, $toLength, $padChar)
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

    public static function toNumeric($value)
    {
        if (is_null($value)) {
            return "";
        }
        if ((string) $value == "0") {
            return "000";
        }
        if (!is_numeric($value)) {
            throw new ArgumentException("A non well formed numeric value encountered!");
        }
        $locale = Locale::getDefault();
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $currency = $formatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);

        return ltrim(preg_replace("/[^0-9]/","", $formatter->formatCurrency($value, $currency)), "0");
    }

    /**
     * @param string $str
     *
     * @return float|int
     */
    public static function toAmount($str)
    {
        if (empty($str)) {
            return 0;
        }

        return $str / 100;
    }

    /**
     * Strip all non-numeric characters
     *
     * @param string $value
     *
     * @return mixed
     */
    public static function validateToNumber($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }

    /**
     * @param string $hexString
     * @return mixed
     */
    public static function bytesFromHex($hexString)
    {
        return pack("H*" , strtolower($hexString));
    }

    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function boolToString($value)
    {
        if (!is_bool($value)) {
            return;
        }

        return json_encode($value);
    }
}
