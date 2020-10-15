<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class TraceResponse implements IResponseSubGroup
{

    public $transactionNumber;
    public $referenceNumber;
    public $timeStamp;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        // Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $this->transactionNumber = isset($response[0]) ? $response[0] : '';
            $this->referenceNumber = isset($response[1]) ? $response[1] : '';
            $this->timeStamp = isset($response[2]) ? $response[2] : '';
        } catch (\Exception $e) {
        }
    }
}
