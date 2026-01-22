<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AvsRequest implements IRequestSubGroup
{

    public ?string $zipCode = null;
    public ?string $address = null;
    
    public function getElementString()
    {
        $requestParams = ['zipCode', 'address'];
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
