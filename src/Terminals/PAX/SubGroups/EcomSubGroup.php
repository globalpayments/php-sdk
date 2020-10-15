<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class EcomSubGroup implements IRequestSubGroup
{

    public $ecomMode;
    public $transactionType;
    public $secureType;
    public $orderNumber;
    public $installments;
    public $currentInstallment;
    
    public function getElementString()
    {
        $requestParams = ['ecomMode', 'transactionType', 'secureType', 'orderNumber', 'installments',
            'currentInstallment'];
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
