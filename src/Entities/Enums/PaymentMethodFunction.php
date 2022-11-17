<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentMethodFunction extends Enum
{
    const PRIMARY_PAYOUT = 'PRIMARY_PAYOUT';
    const SECONDARY_PAYOUT = 'SECONDARY_PAYOUT';
    const ACCOUNT_ACTIVATION_FEE = 'ACCOUNT_ACTIVATION_FEE';
}