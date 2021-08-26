<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentMethodName extends Enum
{
    const APM = 'APM';
    const DIGITAL_WALLET = 'DIGITAL WALLET';
    const CARD = 'CARD';
    /**
     * ACH transaction
     */
    const BANK_TRANSFER = 'BANK TRANSFER';
}