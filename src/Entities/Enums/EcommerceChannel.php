<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Identifies eCommerce vs mail order / telephone order (MOTO) transactions.
 */
class EcommerceChannel extends Enum
{
    /**
     * Identifies eCommerce transactions.
     */
    const ECOM = 'ECOM';

    /**
     * Identifies mail order / telephone order (MOTO) transactions.
     */
    const MOTO = 'MOTO';
}
