<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class HostDecision extends Enum
{
    const APPROVED = 'Approved';
    const DECLINED = 'Declined';
    const FAILED_TO_CONNECT = 'Failed to Connect';
    const STAND_IN = 'Stand IN';
}