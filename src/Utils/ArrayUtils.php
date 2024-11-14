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
    public static function array_remove_empty(?array $haystack): array
    {
        if (is_null($haystack)) {
            return [];
        }
        foreach ($haystack as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $v = (array)$value;
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

    public static function jsonToArray(object $response) : array
    {
        return json_decode(json_encode($response), true);
    }
}