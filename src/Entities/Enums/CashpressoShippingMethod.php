<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CashpressoShippingMethod extends Enum
{
    const DELIVERY = 'DELIVERY';
    const PICKUP = 'PICKUP';
    const PICKUP_BOX = 'PICKUP_BOX';
    const POSTOFFICE = 'POSTOFFICE';
}