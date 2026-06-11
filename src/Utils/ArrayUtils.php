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
        if ($haystack === null) {
            return [];
        }
        // Extract keys that should preserve empty values
        $preserve = [];
        if (isset($haystack['__PRESERVE_EMPTY__']) && is_array($haystack['__PRESERVE_EMPTY__'])) {
            $preserve = $haystack['__PRESERVE_EMPTY__'];
            unset($haystack['__PRESERVE_EMPTY__']);
        }
        foreach ($haystack as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $haystack[$key] = self::array_remove_empty((array)$value);
            }

            if (empty($haystack[$key])) {
                if (in_array($key, $preserve, true) && is_array($haystack[$key])) {
                    continue;
                }
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