<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CashpressoPaymentPlan extends Enum
{
    const PAY_IN_3_INSTALLMENTS = 'PAY_IN_3_INSTALLMENTS';
    const PAY_30_DAYS = 'PAY_30_DAYS';
}