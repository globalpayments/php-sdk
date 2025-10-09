<?php
namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Enum for allowed payment methods in hosted payment pages, goes in order.transaction_configuration.allowed_payment_methods
 * 
 */
class HPPAllowedPaymentMethods extends Enum
{
    /**
     * Standard card payment method
     */
    const CARD = 'CARD';
    
    /**
     * Bank payment method
     */
    const BANK_PAYMENT = 'BANK_PAYMENT';
    
    /**
     * BLIK payment method
     */
    const BLIK = 'BLIK';

    /**
     * PayU payment method
     */
    const PAYU = 'PAYU';
}
