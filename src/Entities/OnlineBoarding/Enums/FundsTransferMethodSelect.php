<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class FundsTransferMethodSelect extends Enum
{
    const DEPOSITS_AND_FEES = 'Deposits & Fees';
    const DEPOSITS_ONLY_SPLIT = 'Deposits Only - (Split*)';
    const FEES = 'Fees';
}
