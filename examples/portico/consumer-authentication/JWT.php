<?php

/**
 * JWT encoding
 *
 * PHP Version 5.2+
 *
 * @category Authentication
 * @package  HPS
 * @author   Heartland Payment Systems <entapp_devportal@e-hps.com>
 * @license  Custom https://github.com/hps/heartland-php/blob/master/LICENSE.txt
 * @link     https://developer.heartlandpaymentsystems.com
 */

class JWT
{
    /**
     * Encodes a JWT with a `$key` and a `$payload`
     *
     * @param string $key     key used to sign the JWT
     * @param mixed  $payload payload to be included
     *
     * @return string
     */
    public static function encode($key = '', $payload = array())
    {
        $header = array('typ' => 'JWT', 'alg' => 'HS256');

        $parts = array(
            self::urlsafeBase64Encode(json_encode($header)),
            self::urlsafeBase64Encode(json_encode($payload)),
        );
        $signingData = implode('.', $parts);
        $signature = self::sign($key, $signingData);
        $parts[] = self::urlsafeBase64Encode($signature);

        return implode('.', $parts);
    }

    /**
     * Creates a url-safe base64 encoded AnyValuesToken
     *
     * @param string $data data to be encoded
     *
     * @return string
     */
    public static function urlsafeBase64Encode($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    /**
     * Signs a set of `$signingData` with a given `$key`
     *
     * @param string $key         key used to sign the JWT
     * @param string $signingData data to be signed
     *
     * @return string
     */
    public static function sign($key, $signingData)
    {
        return hash_hmac('sha256', $signingData, $key, true);
    }
};
