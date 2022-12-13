<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class BNPLType extends Enum
{
    const AFFIRM = 'AFFIRM';
    const CLEARPAY = 'CLEARPAY';
    const KLARNA = 'KLARNA';
}