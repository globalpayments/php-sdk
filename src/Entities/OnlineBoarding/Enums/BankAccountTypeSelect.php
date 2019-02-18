<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class BankAccountTypeSelect extends Enum
{
    const CHECKING = 'Checking';
    const SAVINGS = 'Savings';
    const OTHER = 'GL';
}
