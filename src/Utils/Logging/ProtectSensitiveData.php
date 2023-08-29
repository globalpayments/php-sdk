<?php

namespace GlobalPayments\Api\Utils\Logging;

use GlobalPayments\Api\Entities\MaskedValueCollection;

class ProtectSensitiveData
{
    private static MaskedValueCollection $hideValueCollection;

    public static function hideValue($key, $value, $unmaskedLastChars = 0, $unmaskedFirstChars = 0) : array
    {
        return (self::$hideValueCollection
            ?? (self::$hideValueCollection =
                new MaskedValueCollection()))->hideValue($key, $value, $unmaskedLastChars, $unmaskedFirstChars);
    }

    public static function hideValues(array $list, $unmaskedLastChars = 0, $unmaskedFirstChars = 0) : array
    {
        foreach ($list as $key => $value) {
            if(empty(self::$hideValueCollection)) {
                self::$hideValueCollection = new MaskedValueCollection();
            }
            $maskedList = self::$hideValueCollection->hideValue($key, $value, $unmaskedLastChars, $unmaskedFirstChars);
        }

        return $maskedList ?? [];
    }

}