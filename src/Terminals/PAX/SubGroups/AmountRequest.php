<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AmountRequest implements IRequestSubGroup
{

    public float|int|string|null $transactionAmount = null;
    public float|int|string|null $tipAmount = null;
    public float|int|string|null $cashBackAmount = null;
    public float|int|string|null $merchantFee = null;
    public float|int|string|null $taxAmount = null;
    public float|int|string|null $fuelAmount = null;
    
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
