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
    const CREATE_ACCOUNT = 17179869184; // 1 << 34;
    const RESET_PASSWORD = 34359738368; // 1 << 35;
    const RENEW_ACCOUNT = 68719476736; // 1 << 36;
    const UPDATE_OWNERSHIP_DETAILS = 137438953472; // 1 << 37;
    const UPLOAD_CHARGEBACK_DOCUMENT = 274877906944; // 1 << 38;
    const UPLOAD_DOCUMENT = 549755813888; // 1 << 39;
    const OBTAIN_SSO_KEY = 1099511627776; // 1 << 40;
    const UPDATE_BANK_ACCOUNT_OWNERSHIP = 2199023255552; // 1 << 41;
    const ADD_FUNDS = 4398046511104;//1 << 42;
    const SWEEP_FUNDS = 8796093022208;//1 << 43;
    const ADD_CARD_FLASH_FUNDS = 17592186044416;//1 << 44;
    const PUSH_MONEY_FLASH_FUNDS = 35184372088832;//1 << 45;
    const DISBURSE_FUNDS = 70368744177664;//1 << 46;
    const SPEND_BACK = 140737488355328;//1 << 47;
    const REVERSE_SPLITPAY = 281474976710656;//1 << 48;
    const SPLIT_FUNDS = 562949953421312;//1 << 49;
    const GET_ACCOUNT_DETAILS = 1125899906842624;//1 << 50;
    const GET_ACCOUNT_BALANCE = 2251799813685248;//1 << 51;    
    const DETOKENIZE = 4503599627370496; //1 << 52
    const DISPUTE_ACCEPTANCE = 9007199254740992; // 1 << 53
    const DISPUTE_CHALLENGE = 18014398509481984; // 1 << 54
}
