<?php

namespace GlobalPayments\Api\Utils;

use DateTime;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use ReflectionClass;

/**
 * Utils for the auto-generation of fields, for example the SHA1 hash.
 */
class GenerationUtils
{

    /**
     * Generate a hash, required for all messages sent to Realex to prove it
     * was not tampered with.
     *
     * Each message sent to Realex should have a hash attached. For a message
     * using the remote interface this is generated from the TIMESTAMP,
     * MERCHANT_ID, ORDER_ID, AMOUNT, and CURRENCY fields concatenated together
     * with '.' in between each field. This confirms the message comes from the
     * client.
     *
     * Hashing takes a string as input and produces a fixed size number (160
     * bits for SHA-1 which this implementation uses). This number is a hash of
     * the input, and a small change in the input results in a substantial
     * change in the output. The functions are thought to be secure in the
     * sense that it requires an enormous amount of computing power and time to
     * find a string that hashes to the same value. In others words, there's no
     * way to decrypt a secure hash. Given the larger key size, this
     * implementation uses SHA-1 which we prefer that you use, but Realex has
     * retained compatibilty with MD5 hashing for compatibility with older
     * systems.
     *
     * To construct the hash for the remote interface follow this procedure:
     *
     * 1. Form a string by concatenating the above fields with a period ('.') in
     *    the following order
     *
     *     (TIMESTAMP.MERCHANT_ID.ORDER_ID.AMOUNT.CURRENCY)
     *
     * 2. Like so (where a field is empty an empty string '' is used):
     *
     *     (20120926112654.thestore.ORD453-11.29900.EUR)
     *
     * 3. Get the hash of this string (SHA-1 shown below):
     *
     *     (b3d51ca21db725f9c7f13f8aca9e0e2ec2f32502)
     *
     * 4. Create a new string by concatenating this string and your shared secret
     *    using a period.
     *
     *     (b3d51ca21db725f9c7f13f8aca9e0e2ec2f32502.mysecret)
     *
     * 5. Get the hash of this value. This is the value that you send to Realex.
     *
     *     (3c3cac74f2b783598b99af6e43246529346d95d1)
     *
     * This method takes the pre-built string of concatenated fields and the
     * secret and returns the SHA-1 hash to be placed in the request sent to
     * Realex.
     *
     * @param string $secret The shared secret
     * @param string $toHash The value to be hashed
     *
     * @return string The hash as a hex string
     */
    public static function generateHash($secret, $toHash = null)
    {
        if ($toHash === null) {
            return sha1($secret);
        }

        //first pass hashes the String of required fields
        $toHashFirstPass = sha1($toHash);

        //second pass takes the first hash, adds the secret and hashes again
        $toHashSecondPass = $toHashFirstPass . '.' . $secret;

        return sha1($toHashSecondPass);
    }

    /**
     * Generate the current date/timestamp in the string format (YYYYMMDDHHSS)
     * required in a request to Realex.
     *
     * @return string current timestamp in YYYYMMDDHHSS format
     */
    public static function generateTimestamp()
    {
        $date = new DateTime();

        return $date->format('YmdHis');
    }

    /**
     * Order Id for a initial request should be unique per client ID. This method
     * generates a unique order ID using the PHP GUID function and then converts
     * it to base64 to shorten the length to 22 characters. Order Id for a subsequent
     * request (void, rebate, settle etc.) should use the order Id of the initial
     * request.
     *
     * The order ID uses the PHP GUID (globally unique identifier), so in theory,
     * it may not be unique but the odds of this are extremely remote (see
     * https://en.wikipedia.org/wiki/Globally_unique_identifier)
     *
     * @return string
     */
    public static function generateOrderId()
    {
        $uuid = self::getGuid();
        $mostSignificantBits = substr($uuid, 0, 8);
        $leastSignificantBits = substr($uuid, 23, 8);

        return substr(
            base64_encode($mostSignificantBits . $leastSignificantBits),
            0,
            22
        );
    }

    public static function generateRecurringKey($key = null)
    {
        if ($key !== null) {
            return $key;
        }

        $uuid = self::getGuid();
        return strtolower($uuid);
    }

    public static function getGuid()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function convertArrayToJson($request, $hppVersion = '')
    {
        if ($hppVersion != HppVersion::VERSION_2) {
            $request = array_map('base64_encode', $request);
        }
        return json_encode($request);
    }

    public static function decodeJson($json, $returnArray = true, $hppVersion = '')
    {
        if ($hppVersion != HppVersion::VERSION_2) {
            return array_map('base64_decode', json_decode($json, true));
        }
        return json_decode($json, $returnArray);
    }

    public static function convertObjectToArray($object)
    {
        $reflectionClass = new ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            if (!empty($property->getValue($object))) {
                $array[$property->getName()] = $property->getValue($object);
            }
            $property->setAccessible(false);
        }

        return $array;
    }
}
