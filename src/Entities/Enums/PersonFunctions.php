<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PersonFunctions extends Enum
{
    const APPLICANT = 'APPLICANT';
    const BENEFICIAL_OWNER = 'BENEFICIAL_OWNER';
    const PAYMENT_METHOD_OWNER = 'PAYMENT_METHOD_OWNER';
}