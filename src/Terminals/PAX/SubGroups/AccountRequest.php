<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AccountRequest implements IRequestSubGroup
{

    public $accountNumber;
    public $expd;
    public $cvvCode;
    public $ebtType;
    public $voucherNumber;
    public $dupOverrideFlag;
    
    public function getElementString()
    {
        $requestParams = ['accountNumber', 'expd', 'cvvCode',
            'ebtType', 'voucherNumber', 'dupOverrideFlag'];
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
