<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\PhoneNumberType;

class PhoneNumber
{
    public $countryCode;
    public $number;

    /** @var string|PhoneNumberType */
    public $type;

    public function __construct($countryCode, $number, string|PhoneNumberType $type)
    {
        $this->countryCode = $countryCode;
        $this->number = $number;
        $this->type = $type;
    }
}