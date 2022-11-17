<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class DecoupledFlowRequest extends Enum
{
    const DECOUPLED_PREFERRED = 'DECOUPLED_PREFERRED';
    const DO_NOT_USE_DECOUPLED = 'DO_NOT_USE_DECOUPLED';
}