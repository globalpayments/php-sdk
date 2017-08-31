<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

abstract class ExceptionCodes extends Enum
{
    // general codes
    const AUTHENTICATION_ERROR = 0;
    const INVALID_CONFIGURATION = 1;

    // input codes
    const INVALID_AMOUNT = 2;
    const MISSING_CURRENCY = 3;
    const INVALID_CURRENCY = 4;
    const INVALID_DATE = 5;
    const MISSING_CHECK_NAME = 28;
    const INVALID_PHONE_NUMBER = 33;
    const INVALID_ZIP_CODE = 34;
    const INVALID_EMAIL_ADDRESS = 35;
    const INVALID_INPUT_LENGTH = 36;

    // gateway codes
    const UNKNOWN_GATEWAY_ERROR = 6;
    const INVALID_ORIGINAL_TRANSACTION = 7;
    const NO_OPEN_BATCH = 8;
    const INVALID_CPC_DATA = 9;
    const INVALID_CARD_DATA = 10;
    const INVALID_NUMBER = 11;
    const GATEWAY_TIMEOUT = 12;
    const UNEXPECTED_GATEWAY_RESPONSE = 13;
    const GATEWAY_TIMEOUT_REVERSAL_ERROR = 14;
    const GATEWAY_ERROR = 31;
    const UNEXPECTED_GATEWAY_ERROR = 32;

    // credit issuer codes
    const INCORRECT_NUMBER = 15;
    const EXPIRED_CARD = 16;
    const INVALID_PIN = 17;
    const PIN_ENTRIES_EXCEEDED = 18;
    const INVALID_EXPIRY = 19;
    const PIN_VERIFICATION = 20;
    const ISSUER_TIMEOUT = 21;
    const INCORRECT_CVC = 22;
    const CARD_DECLINED = 23;
    const PROCESSING_ERROR = 24;
    const ISSUER_TIMEOUT_REVERSAL_ERROR = 25;
    const UNKNOWN_CREDIT_ERROR = 26;
    const POSSIBLE_FRAUD_DETECTED = 27;

    // gift codes
    const UNKNOWN_GIFT_ERROR = 29;
    const PARTIAL_APPROVAL = 30;
}
