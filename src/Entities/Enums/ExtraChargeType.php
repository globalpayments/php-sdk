<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ExtraChargeType extends Enum
{
    const RESTAURANT = 1;
    const GIFT_SHOP = 2;
    const MINI_BAR = 3;
    const TELEPHONE = 4;
    const LAUNDRY = 5;
    const OTHER = 10;
}