<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Ecommerce indicator identifying type of ecommerce transaction.
 * Applicable only for card-not-present transactions in US and CA only.
 */
class EcommerceIndicator extends Enum
{
    const ECOMMERCE_INDICATOR_1 = "1";
    const ECOMMERCE_INDICATOR_2 = "2";
    const ECOMMERCE_INDICATOR_3 = "3";
    const ECOMMERCE_INDICATOR_5 = "5";
    const ECOMMERCE_INDICATOR_7 = "7";
}
