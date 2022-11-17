<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UserStatus extends Enum
{
    const ACTIVE = 'ACTIVE';
    const INACTIVE = 'INACTIVE';
    const UNDER_REVIEW = 'UNDER_REVIEW';
    const DECLINED = 'DECLINED';
}