<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CommercialRequest implements IRequestSubGroup
{

    public ?string $poNumber = null;
    public ?string $customerCode = null;
    public ?bool $taxExempt = null;
    public ?string $taxExemptId = null;
    
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
