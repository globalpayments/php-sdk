<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class Secure3dStatus extends Enum
{
    const SUCCESS_AUTHENTICATED = 'SUCCESS_AUTHENTICATED';
    const SUCCESS_ATTEMPT_MADE = 'SUCCESS_ATTEMPT_MADE';
    const NOT_AUTHENTICATED = 'NOT_AUTHENTICATED';
    const FAILED = 'FAILED';
    const CHALLENGE_REQUIRED = 'CHALLENGE_REQUIRED';
    const NOT_ENROLLED = 'NOT_ENROLLED';
    const ENROLLED = 'ENROLLED';
}