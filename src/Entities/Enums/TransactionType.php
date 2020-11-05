<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionType extends Enum
{
    const DECLINE = 1; // 1 << 0
    const VERIFY = 2; // 1 << 1
    const CAPTURE = 4; // 1 << 2
    const AUTH = 8; // 1 << 3
    const REFUND = 16; // 1 << 4
    const REVERSAL = 32; // 1 << 5
    const SALE = 64; // 1 << 6
    const EDIT = 128; // 1 << 7
    const VOID = 256; // 1 << 8
    const ADD_VALUE = 512; // 1 << 9
    const BALANCE = 1024; // 1 << 10
    const ACTIVATE = 2048; // 1 << 11
    const ALIAS = 4096; // 1 << 12
    const REPLACE = 8192; // 1 << 13
    const REWARD = 16384; // 1 << 14
    const DEACTIVATE = 32768; // 1 << 15
    const BATCH_CLOSE = 65536; // 1 << 16
    const CREATE = 131072; // 1 << 17
    const DELETE = 262144; // 1 << 18
    const FETCH = 524288; // 1 << 19
    const SEARCH = 1048576; // 1 << 20
    const HOLD = 2097152; // 1 << 21
    const RELEASE = 4194304; // 1 << 22
    const DCC_RATE_LOOKUP = 8388608; //1 << 23
    const VERIFY_ENROLLED = 16777216; //1 << 24
    const VERIFY_SIGNATURE = 33554432; // 1 << 25
    const TOKEN_DELETE = 67108864; // 1 << 26
    const VERIFY_AUTHENTICATION = 134217728; // 1 << 27
    const INITIATE_AUTHENTICATION = 268435456; // 1 << 28
    const DATA_COLLECT = 536870912; // 1 << 29
    const PRE_AUTH_COMPLETION = 1073741824; // 1 << 30
    const TOKEN_UPDATE = 2147483648; // 1 << 31
    const BENEFIT_WITHDRAWAL = 4294967296; // 1 <<32
    const TOKENIZE = 8589934592; // 1 << 33;
}
