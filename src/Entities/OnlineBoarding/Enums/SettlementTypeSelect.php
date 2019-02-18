<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class SettlementTypeSelect extends Enum
{
    const DAILY_SPLIT = 'DailySplit';
    const MONTHLY_BILLING = 'Monthly';
    const DAILY_NET = 'DailyNet';
}
