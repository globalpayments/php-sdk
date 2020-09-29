<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\Interfaces\ILogManagement;

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
        
    public static function buildAdminMessage($messageType, $otherParams = null)
    {
        $message = chr(ControlCodes::STX);
        
        $message .= $messageType;
        $message .= chr(ControlCodes::FS);
        
        $message .= '1.35';
        $message .= chr(ControlCodes::FS);
        
        if (!empty($otherParams)) {
            $message .= implode(chr(ControlCodes::FS), $otherParams);
            $message .= chr(ControlCodes::FS);
        }
        
        $message .= chr(ControlCodes::ETX);
        $message .= self::calculateLRC(trim($message));
        
        return trim($message);
    }
    
    public static function buildMessage($message)
    {
        $message = chr(ControlCodes::STX) . $message;
        $message .= chr(ControlCodes::ETX);
        
        $message .= self::calculateLRC(trim($message));
        
        return trim($message);
    }
    
    public static function calculateLRC($buffer)
    {
        if (! empty($buffer)) {
            $length = strlen($buffer);
            if ($buffer[$length - 1] != ControlCodes::ETX) {
                $length --;
            }

            $lrc = 0;
            for ($i = 1; $i < strlen($buffer); $i ++) {
                $lrc = ($lrc ^ ord($buffer[$i]));
            }
            return chr($lrc);
        } else {
            return '';
        }
    }

    public static function formatAmount($amount)
    {
        if ($amount === null) {
            return "";
        } elseif ($amount === 0) {
            return "000";
        } else {
            return preg_replace('/[^0-9]/', '', sprintf('%01.2f', $amount));
        }
    }
    
    public static function reformatAmount($amount)
    {
        return $amount / 100;
    }
    
    public static function manageLog($logProvider, $message = '', $backTrace = false)
    {
        if ($logProvider !== null && $logProvider instanceof ILogManagement) {
            $trace = '';
            if ($backTrace === true) {
                ob_start();
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                $trace = ob_get_contents();
                ob_end_clean();
            }
            
            $logProvider->setLog($message, $trace);
        }
    }
}
