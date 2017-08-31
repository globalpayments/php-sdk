<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class InquiryType extends Enum
{
    const STANDARD = 'STANDARD';
    const FOODSTAMP = 'FOODSTAMP';
    const CASH = 'CASH';
    const POINTS = 'POINTS';
}
