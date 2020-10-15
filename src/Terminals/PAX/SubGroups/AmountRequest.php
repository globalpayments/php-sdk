<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AmountRequest implements IRequestSubGroup
{

    public $transactionAmount;
    public $tipAmount;
    public $cashBackAmount;
    public $merchantFee;
    public $taxAmount;
    public $fuelAmount;
    
    public function getElementString()
    {
        $requestParams = ['transactionAmount', 'tipAmount', 'cashBackAmount',
            'merchantFee', 'taxAmount', 'fuelAmount'];
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
