<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Entities\Enums\TrackNumber;

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
}
