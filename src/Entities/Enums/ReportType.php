<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ReportType extends Enum
{
    const FIND_TRANSACTIONS = 1;
    const ACTIVITY = 2; // 1 << 1;
    const TRANSACTION_DETAIL = 128; // 1 << 7;
    const FIND_DEPOSITS = 256; //1 << 8
    const FIND_DISPUTES = 512; // 1 << 9
    const FIND_SETTLEMENT_DISPUTES = 1024; //1 << 10
    const DEPOSIT_DETAIL = 2048; // 1 << 11
    const DISPUTE_DETAIL = 4096; // 1 << 12
    const SETTLEMENT_DISPUTE_DETAIL = 8192; // 1 << 13
    const FIND_SETTLEMENT_TRANSACTIONS = 16384; // 1 << 14
    const FIND_TRANSACTIONS_PAGED = 32768; // 1 << 15
    const FIND_SETTLEMENT_TRANSACTIONS_PAGED = 65536; // 1 << 16
    const FIND_DEPOSITS_PAGED = 131072; // 1 << 17
    const FIND_DISPUTES_PAGED = 262144; // 1 << 18
    const FIND_SETTLEMENT_DISPUTES_PAGED = 524288; // 1 << 19
}
