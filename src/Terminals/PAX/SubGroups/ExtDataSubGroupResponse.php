<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class ExtDataSubGroupResponse implements IResponseSubGroup
{

    public $details;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            foreach ($response as $val) {
                $val = explode('=', $val);
                if (!empty($val[0]) && isset($val[1])) {
                    $key = trim(strtoupper($val[0]));
                    $this->details[$key] = trim($val[1]);
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function getExtValue($key)
    {
        return (!empty($this->details[$key])) ? $this->details[$key] : '';
    }
}
