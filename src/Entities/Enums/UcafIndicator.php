<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UcafIndicator extends Enum
{
    const NOT_SUPPORTED                 = '0';
    const MERCHANT_ONLY                 = '1';
    const FULLY_AUTHENTICATED           = '2';
    const ISSUER_RISK_BASED             = '5';
    const MERCHANT_RISK_BASED           = '6';
    const PARTIAL_SHIPMENT_INCREMENTAL  = '7';
}
