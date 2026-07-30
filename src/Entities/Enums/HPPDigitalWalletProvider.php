<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Enum for digital wallet providers available in hosted payment pages.
 * Used in order.payment_method_configuration.digital_wallets.provider
 *
 * Provider value casing is API-driven and must be preserved exactly.
 */
class HPPDigitalWalletProvider extends Enum
{
    /**
     * Google Pay digital wallet
     */
    const GOOGLEPAY = 'googlepay';

    /**
     * Apple Pay digital wallet
     */
    const APPLEPAY = 'applepay';

    /**
     * Click to Pay digital wallet
     */
    const CLICK_TO_PAY = 'CLICK_TO_PAY';
}
