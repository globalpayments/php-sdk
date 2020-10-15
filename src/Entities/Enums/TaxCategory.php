<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TaxCategory extends Enum
{
    const SERVICE       = "SERVICE";
    const DUTY          = "DUTY";
    const VAT           = "VAT";
    const ALTERNATE     = "ALTERNATE";
    const NATIONAL      = "NATIONAL";
    const TAX_EXEMPT    = "TAX_EXEMPT";
}
