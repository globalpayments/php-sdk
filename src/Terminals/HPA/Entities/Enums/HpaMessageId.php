<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class HpaMessageId extends Enum
{
    const LANE_OPEN = "LaneOpen";
    const LANE_CLOSE = "LaneClose";
    const RESET = "Reset";
    const REBOOT = "Reboot";
    const BATCH_CLOSE = "CloseBatch";
    const GET_BATCH_REPORT = "GetBatchReport";
    const CREDIT_SALE = "Sale";
    const CREDIT_REFUND = "Refund";
    const CREDIT_VOID = "Void";
    const CARD_VERIFY = "CardVerify";
    const CREDIT_AUTH = "CreditAuth";
    const BALANCE = "BalanceInquiry";
    const ADD_VALUE = "AddValue";
    const TIP_ADJUST = "TipAdjust";
    const GET_INFO_REPORT = "GetAppInfoReport";
    const CAPTURE = "CreditAuthComplete";
    const STARTDOWNLOAD = "Download";
    const EOD = "EOD";
    const LINEITEM = "LineItem";
    const SENDSAF = "SendSAF";
    const STARTCARD = "StartCard";
    const SEND_FILE = "SendFile";
    const GET_DIAGNOSTIC_REPORT = "GetDiagnosticReport";
    const SIGNATURE_FORM = "SignatureForm";
    const GET_LAST_RESPONSE = "GetLastResponse";
}
