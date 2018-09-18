<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ScheduleFrequency extends Enum
{
    const WEEKLY = 'Weekly';
    const BI_WEEKLY = 'Bi-Weekly';
    const SEMI_MONTHLY = 'Semi-Monthly';
    const MONTHLY = 'Monthly';
    const BI_MONTHLY = 'Bi-Monthly';
    const QUARTERLY = 'Quarterly';
    const SEMI_ANNUALLY = 'Semi-Annually';
    const ANNUALLY = 'Annually';
}
