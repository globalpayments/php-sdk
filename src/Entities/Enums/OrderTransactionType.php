<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class OrderTransactionType extends Enum
{
    const GOODS_SERVICE_PURCHASE = 'GOODS_SERVICE_PURCHASE';
    const CHECK_ACCEPTANCE = 'CHECK_ACCEPTANCE';
    const ACCOUNT_FUNDING = 'ACCOUNT_FUNDING';
    const QUASI_CASH_TRANSACTION = 'QUASI_CASH_TRANSACTION';
    const PREPAID_ACTIVATION_AND_LOAD = 'PREPAID_ACTIVATION_AND_LOAD';
}
