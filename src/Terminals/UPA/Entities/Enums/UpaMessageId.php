<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UpaMessageId extends Enum
{

    const SALE = "Sale";
    const VOID = "Void";
    const REFUND = "Refund";
    const EOD = "EODProcessing";
    const SENDSAF = "SendSAF";
    const TIPADJUST = "TipAdjust";
    const CARD_VERIFY = "CardVerify";
    const GET_SAF_REPORT = "GetSAFReport";
    const CANCEL   = "CancelTransaction";
    const REBOOT   = "Reboot";
    const LINEITEM = "LineItemDisplay";
    const REVERSAL = "Reversal";
    const GET_BATCH_REPORT = "GetBatchReport";
    const BALANCE_INQUIRY = "BalanceInquiry";
}
