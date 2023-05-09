<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UsableBalanceMode extends Enum
{
    const AVAILABLE_BALANCE = 'AVAILABLE_BALANCE';
    const AVAILABLE_AND_PENDING_BALANCE = 'AVAILABLE_AND_PENDING_BALANCE';
}