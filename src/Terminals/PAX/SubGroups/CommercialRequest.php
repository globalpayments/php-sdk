<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CommercialRequest implements IRequestSubGroup
{

    public $poNumber;
    public $customerCode;
    public $taxExempt;
    public $taxExemptId;
    
    public function getElementString()
    {
        $requestParams = ['poNumber', 'customerCode', 'taxExempt', 'taxExemptId'];
        $message = '';
        foreach ($requestParams as $val) {
            if (is_null($this->{$val}) === false) {
                $message .= $this->{$val};
            }
            $message .= chr(ControlCodes::US);
        }
        return rtrim($message, chr(ControlCodes::US));
    }
}
