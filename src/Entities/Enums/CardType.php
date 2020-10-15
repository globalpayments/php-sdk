<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardType extends Enum
{
    const VISA          = 'VISA';
    const MASTERCARD    = 'MASTERCARD';
    const DISCOVER      = 'DISCOVER';
    const AMEX          = 'AMEX';
    const JCB           = 'JCB';
    const DINERS        = 'DINERS';
}
