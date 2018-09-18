<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentMethodType extends Enum
{
    const REFERENCE = 0;
    const CREDIT = 1;
    const DEBIT = 2;
    const EBT = 3;
    const CASH = 4;
    const ACH = 5;
    const GIFT = 6;
    const RECURRING = 7;
    const APM = 8;
}
