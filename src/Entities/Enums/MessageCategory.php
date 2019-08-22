<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class MessageCategory extends Enum
{
    const PAYMENT_AUTHENTICATION = "PAYMENT_AUTHENTICATION";
    const NON_PAYMENT_AUTHENTICATION = "NON_PAYMENT_AUTHENTICATION";
}
