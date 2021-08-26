<?php

namespace GlobalPayments\Api\Entities;

class PhoneNumber
{
    public $countryCode;
    public $number;
    public $type;

    public function __construct($countryCode, $number, $type)
    {
        $this->countryCode = $countryCode;
        $this->number = $number;
        $this->type = $type;
    }
}