<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Utils\ReverseEnumMap;

class EnumUtils
{

    
    public static function isDefined($valueType, $value)
    {
        $parsedValue = self::parse($valueType, $value);
        return ($parsedValue !== null);
    }

    public static function parse($valueType, $value)
    {
        $map = new ReverseEnumMap($valueType);
        return $map->get($value);
    }
}
