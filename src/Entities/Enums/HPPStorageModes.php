<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Enumeration class for Hosted Payment Pages storage modes, goes in order.payment_configuration.storage_modes
 * Some comments taken from the documentation
 */
class HPPStorageModes extends Enum
{
    /**
     * Prompt the payer to store their card
     */
    const PROMPT = 'PROMPT';
    
    /**
     * The card information is only stored if the payment method authorization was successful
     */
    const ON_SUCCESS = 'ON_SUCCESS';
    
    /**
     * The card information is always stored irrespective of whether the payment method authorization was successful or not
     */
    const ALWAYS = 'ALWAYS';
}
