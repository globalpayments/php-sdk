<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class CurrencyType extends Enum
{
    const CURRENCY = 'CURRENCY';
    const POINTS = 'POINTS';
    const CASH_BENEFITS = 'CASH_BENEFITS';
    const FOODSTAMPS = 'FOODSTAMPS';
    const VOUCHER = 'VOUCHER';
}
