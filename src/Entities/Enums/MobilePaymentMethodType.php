<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class MobilePaymentMethodType extends Enum
{
    const APPLEPAY = 'apple-pay';
    const GOOGLEPAY = 'pay-with-google';
}
