<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class FraudFilterMode extends Enum
{
    const NONE = 'NONE';
    const OFF = 'OFF';
    const PASSIVE = 'PASSIVE';
    const ACTIVE = 'ACTIVE ';
}
