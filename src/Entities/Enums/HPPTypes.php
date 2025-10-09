<?php
namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Enumeration for Hosted Payment Page types, goes in the root of the request
 */
class HPPTypes extends Enum
{
    /**
     * Represents a third-party page for payments.
     */
    const THIRD_PARTY_PAGE = 'THIRD_PARTY_PAGE';
    
    /**
     * Represents a standard payment page.
     */
    const PAYMENT = 'PAYMENT';
    
    /**
     * Represents a hosted payment page.
     */
    const HOSTED_PAYMENT_PAGE = 'HOSTED_PAYMENT_PAGE';
    
    /**
     * Represents a page for exchanging application credentials.
     */
    const EXCHANGE_APP_CREDENTIALS = 'EXCHANGE_APP_CREDENTIALS';
}
