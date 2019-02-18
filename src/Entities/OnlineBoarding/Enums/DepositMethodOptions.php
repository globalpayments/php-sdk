<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class DepositMethodOptions extends Enum
{
    const STANDARD = 'Standard';
    const BY_BATCH = 'By Batch';
    const BY_CARD_TYPE = 'By Card Type';
}
