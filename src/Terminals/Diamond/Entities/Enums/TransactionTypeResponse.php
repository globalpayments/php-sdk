<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionTypeResponse extends Enum
{
    const UNKNOWN = '0';
    const SALE = '1';
    const PREAUTH = '4';
    const CAPTURE = '5';
    const REFUND = '6';
    const VOID = '10';
    const REPORT = '66';
    const PREAUTH_CANCEL = '82';
    const INCR_AUTH = '86';
}