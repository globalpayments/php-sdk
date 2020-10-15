<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CashierResponse implements IResponseSubGroup
{

    public $clerkId;
    public $shiftId;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        //Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $this->clerkId = isset($response[0]) ? $response[0] : '';
            $this->shiftId = isset($response[1]) ? $response[1] : '';
        } catch (\Exception $e) {
        }
    }
}
