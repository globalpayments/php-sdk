<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class EBT extends Enum
{
    const FOOD_STAMPS_AND_CASH_BENEFITS = 'FoodAndCash';
    const FOOD_STAMPS_ONLY = 'FoodOnly';
    const CASH_BENEFITS_ONLY = 'CashOnly';
}
