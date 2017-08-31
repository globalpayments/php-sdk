<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentSchedule extends Enum
{
    const DYNAMIC = 'Dynamic';
    const FIRST_DAY_OF_THE_MONTH = 'FirstDayOfTheMonth';
    const LAST_DAY_OF_THE_MONTH = 'LastDayOfTheMonth';
}
