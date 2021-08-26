<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PhoneNumberType extends Enum
{
    const HOME = "HOME";
    const WORK = "WORK";
    const SHIPPING = "SHIPPING";
    const MOBILE = "MOBILE";
}