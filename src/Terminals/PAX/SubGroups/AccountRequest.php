<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AccountRequest implements IRequestSubGroup
{

    public ?string $accountNumber = null;
    public ?string $expd = null;
    public ?string $cvvCode = null;
    public ?string $ebtType = null;
    public ?string $voucherNumber = null;
    public ?string $dupOverrideFlag = null;
    
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
