<?php

namespace GlobalPayments\Api\Terminals\Genius\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionIdType extends Enum
{
    const CLIENT_TRANSACTION_ID = "CLIENT_TRANSACTION_ID";
    const GATEWAY_TRANSACTION_ID = "GATEWAY_TRANSACTION_ID";
}
