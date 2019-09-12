<?php

namespace GlobalPayments\Api\Terminals;

class TerminalUtils
{
    public static function findLength($terminalMessage)
    {
        $messageLength = strlen($terminalMessage);

        //convert the integer as byte array
        $lengthBytes = unpack("C*", pack("L", $messageLength));

        //reverse the byte array to form the header in correct order
        $reverseBytes = array_reverse(array_filter($lengthBytes));

        $lengthHeader = '';
        foreach ($reverseBytes as $byte) {
            $lengthHeader .= chr($byte);
        }

        // need 2 bytes in header
        if (strlen($lengthHeader) === 1) {
            $lengthHeader = chr(0x0) . $lengthHeader;
        }

        return $lengthHeader;
    }

    public static function xmlParse($gatewayResponse)
    {
        $gatewayResponse = substr($gatewayResponse, strpos($gatewayResponse, '<SIP>'));
        $gatewayResponse = str_replace(['&', '&apos;'], ['&amp;', "'"], $gatewayResponse);

        //convert xml to PHP array
        $responseXml = simplexml_load_string($gatewayResponse);
        $responseJson = json_encode($responseXml);
        $responseData = json_decode($responseJson, true);

        return $responseData;
    }

    public static function formatAmount($amount)
    {
        return preg_replace('/[^0-9]/', '', sprintf('%01.2f', $amount));
    }
    
    public static function reformatAmount($amount)
    {
        return $amount / 100;
    }
}
