<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class EcomSubGroup implements IRequestSubGroup
{

    public ?string $ecomMode = null;
    public ?string $transactionType = null;
    public ?string $secureType = null;
    public ?string $orderNumber = null;
    public ?int $installments = null;
    public ?int $currentInstallment = null;
    
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
