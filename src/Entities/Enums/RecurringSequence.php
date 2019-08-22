<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class RecurringSequence extends Enum
{
    const FIRST = 'FIRST';
    const SUBSEQUENT = 'SUBSEQUENT';
    const LAST = 'LAST';
}
