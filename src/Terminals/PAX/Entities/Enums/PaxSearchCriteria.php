<?php

namespace GlobalPayments\Api\Terminals\PAX\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaxSearchCriteria extends Enum
{

    const TRANSACTION_TYPE = "TransactionType";
    const CARD_TYPE = "CardType";
    const RECORD_NUMBER = "RecordNumber";
    const TERMINAL_REFERENCE_NUMBER = "TerminalReferenceNumber";
    const AUTH_CODE = "AuthCode";
    const REFERENCE_NUMBER = "ReferenceNumber";
    const MERCHANT_ID = "MerchantId";
    const MERCHANT_NAME = "MerchantName";
}
