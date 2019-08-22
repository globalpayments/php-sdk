<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class DeliveryTimeFrame extends Enum
{
    const ELECTRONIC_DELIVERY = 'ELECTRONIC_DELIVERY';
    const SAME_DAY = 'SAME_DAY';
    const OVERNIGHT = 'OVERNIGHT';
    const TWO_DAYS_OR_MORE = 'TWO_DAYS_OR_MORE';
}
