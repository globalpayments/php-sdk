<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CommercialResponse implements IResponseSubGroup
{

    public $poNumber;
    public $customerCode;
    public $taxExempt;
    public $taxExemptId;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        //Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $this->poNumber = isset($response[0]) ? $response[0] : '';
            $this->customerCode = isset($response[1]) ? $response[1] : '';
            $this->taxExempt = isset($response[2]) ? $response[2] : '';
            $this->taxExemptId = isset($response[3]) ? $response[3] : '';
        } catch (\Exception $e) {
        }
    }
}
