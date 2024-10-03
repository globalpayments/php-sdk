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
    const BANK_TRANSFER = 'BANK_TRANSFER';

    /** Open Banking transaction */
    const BANK_PAYMENT = 'BANK PAYMENT';

    /** @var Buy Now Pay Later (BNPL) transaction */
    const BNPL = 'BNPL';
}