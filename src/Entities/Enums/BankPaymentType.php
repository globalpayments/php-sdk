<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class BankPaymentType extends Enum
{
    const FASTERPAYMENTS = 'FASTERPAYMENTS';
    const SEPA = 'SEPA';
}