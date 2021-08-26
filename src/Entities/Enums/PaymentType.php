<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentType extends Enum
{
    const REFUND = 'REFUND';
    const SALE = 'SALE';
}