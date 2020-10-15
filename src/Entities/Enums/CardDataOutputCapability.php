<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardDataOutputCapability extends Enum
{
    const NONE                  = 'NONE';
    const MAGNETIC_STRIPE_WRITE = 'MAGNETIC_STRIPE_WRITE';
    const ICC                   = 'ICC';
    const OTHER                 = 'OTHER';
}
