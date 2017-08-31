<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CheckType extends Enum
{
    const PERSONAL = 0;
    const BUSINESS = 1;
    const PAYROLL = 2;
}
