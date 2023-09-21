<?php

namespace GlobalPayments\Api\Terminals\Genius\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class MitcRequestType extends Enum
{
    const CARD_PRESENT_SALE = "CARD_PRESENT_SALE";
    const CARD_PRESENT_REFUND = "CARD_PRESENT_REFUND";
    const REPORT_SALE_GATEWAY_ID = "REPORT_SALE_GATEWAY_ID";
    const REPORT_SALE_CLIENT_ID = "REPORT_SALE_CLIENT_ID";
    const REPORT_REFUND_GATEWAY_ID = "REPORT_REFUND_GATEWAY_ID";
    const REPORT_REFUND_CLIENT_ID = "REPORT_REFUND_CLIENT_ID";
    const REFUND_BY_CLIENT_ID = "REFUND_BY_CLIENT_ID";
    const VOID_CREDIT_SALE = "VOID_CREDIT_SALE";
    const VOID_DEBIT_SALE = "VOID_DEBIT_SALE";
    const VOID_REFUND = "VOID_REFUND";
}
