<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaymentMethodProgram extends Enum
{
    const ASSURED_RESERVATION = 'ASSURED_RESERVATION';
    const CARD_DEPOSIT = 'CARD_DEPOSIT';
    const PURCHASE = 'PURCHASE';
    const OTHER = 'OTHER';
}