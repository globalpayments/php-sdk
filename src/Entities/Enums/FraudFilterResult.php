<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class FraudFilterResult extends Enum
{
    const HOLD = 'HOLD';
    const PASS = 'PASS';
    const BLOCK = 'BLOCK';
    const NOT_EXECUTED = 'NOT_EXECUTED';
    const ERROR = 'ERROR';
    const RELEASE_SUCCESSFUL = 'RELEASE_SUCCESSFULL';
    const HOLD_SUCCESSFUL = 'HOLD_SUCCESSFULL';
}