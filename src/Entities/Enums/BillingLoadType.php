<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class BillingLoadType extends enum  
{
    const NONE = 'NONE';
    const BILLS = 'BILLS';
    const SECURE_PAYMENT = 'SECURE_PAYMENT';
}