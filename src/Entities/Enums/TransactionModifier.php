<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionModifier extends Enum
{

    const NONE = 0;
    const INCREMENTAL = 1;
    const ADDITIONAL = 2;
    const OFFLINE = 3;
    const LEVEL_II = 4;
    const FRAUD_DECLINE = 5;
    const CHIP_DECLINE = 6;
    const CASH_BACK = 7;
    const VOUCHER = 8;
    const RECURRING = 9;
    const HOSTEDREQUEST = 10;
}
