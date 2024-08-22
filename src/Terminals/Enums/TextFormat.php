<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class TextFormat extends Enum
{
    const NUMERIC = 'Numeric';
    const PASSWORD = 'Password';
    const ALPHANUMERIC = 'Alphanumeric';
}