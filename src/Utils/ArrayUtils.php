<?php

namespace GlobalPayments\Api\Utils;

class ArrayUtils
{
    /**
     * Remove empty <key,value> from array. "0" and "false" will not be removed
     *
     * @param array $haystack
     *
     * @return array
     */
    public static function array_remove_empty($haystack)
    {
        if (is_null($haystack)) {
            return [];
        }
        foreach ($haystack as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $v = (array) $haystack[$key];
                $haystack[$key] = self::array_remove_empty($v);
            }
            if (empty($haystack[$key])) {
                if (is_null($haystack[$key]) || is_array($haystack[$key]) || $haystack[$key] === '') {
                    unset($haystack[$key]);
                }
            }
        }

        return $haystack;
    }
}