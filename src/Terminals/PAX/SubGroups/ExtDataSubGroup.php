<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class ExtDataSubGroup implements IRequestSubGroup
{

    public $details;
    
    public function getElementString()
    {
        $message = '';
        if (!empty($this->details)) {
            foreach ($this->details as $key => $val) {
                $message .= sprintf("%s=%s%s", $key, $val, chr(ControlCodes::US));
            }
        }
        return rtrim($message, chr(ControlCodes::US));
    }
}
