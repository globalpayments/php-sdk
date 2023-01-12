<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class IntervalToExpire extends Enum
{
    const WEEK = 'WEEK';
    const DAY = 'DAY';
    const TWELVE_HOURS = '12_HOURS';
    const SIX_HOURS = '6_HOURS';
    const THREE_HOURS = '3_HOURS';
    const ONE_HOUR = '1_HOUR';
    const THIRTY_MINUTES = '30_MINUTES';
    const TEN_MINUTES = '10_MINUTES';
    const FIVE_MINUTES = '5_MINUTES';
}