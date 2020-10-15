<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardDataSource extends Enum
{
    const SWIPE             = "SWIPE";
    const NFC               = "NFC";
    const EMV               = "EMV";
    const EMV_CONTACTLESS   = "EMV_CONTACTLESS";
    const FALLBACK_SWIPE    = "FALLBACK_SWIPE";
    const BAR_CODE          = "BAR_CODE";
    const MANUAL            = "MANUAL";
    const PHONE             = "PHONE";
    const MAIL              = "MAIL";
    const INTERNET          = "INTERNET";
}
