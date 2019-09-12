<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionType extends Enum
{
    const DECLINE = 1 << 0;
    const VERIFY = 1 << 1;
    const CAPTURE = 1 << 2;
    const AUTH = 1 << 3;
    const REFUND = 1 << 4;
    const REVERSAL = 1 << 5;
    const SALE = 1 << 6;
    const EDIT = 1 << 7;
    const VOID = 1 << 8;
    const ADD_VALUE = 1 << 9;
    const BALANCE = 1 << 10;
    const ACTIVATE = 1 << 11;
    const ALIAS = 1 << 12;
    const REPLACE = 1 << 13;
    const REWARD = 1 << 14;
    const DEACTIVATE = 1 << 15;
    const BATCH_CLOSE = 1 << 16;
    const CREATE = 1 << 17;
    const DELETE = 1 << 18;
    const FETCH = 1 << 19;
    const SEARCH = 1 << 20;
    const HOLD = 1 << 21;
    const RELEASE = 1 << 22;
    const DCC_RATE_LOOKUP = 1 << 23;
    const VERIFY_ENROLLED = 1 << 24;
    const VERIFY_SIGNATURE = 1 << 25;
    const TOKEN_DELETE = 1 << 26;
    const VERIFY_AUTHENTICATION = 1 << 27;
    const INITIATE_AUTHENTICATION = 1 << 28;
    const DATA_COLLECT = 1 << 29;
    const PRE_AUTH_COMPLETION = 1 << 30;
    const TOKEN_UPDATE = 1 << 31;
}
