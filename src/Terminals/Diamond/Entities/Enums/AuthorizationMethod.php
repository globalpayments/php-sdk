<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class AuthorizationMethod extends Enum
{
    const PIN = 'A';
    const SIGNATURE = '@';
    const PIN_AND_SIGNATURE = 'B';
    const NO_AUTH_METHOD = '?';
}