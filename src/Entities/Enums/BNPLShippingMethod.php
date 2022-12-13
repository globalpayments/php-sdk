<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class BNPLShippingMethod extends Enum
{
    const DELIVERY = 'DELIVERY';
    const COLLECTION = 'COLLECTION';
    const EMAIL = 'EMAIL';
}