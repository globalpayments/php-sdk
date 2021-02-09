<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Entities\Enums\TrackNumber;
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
}
