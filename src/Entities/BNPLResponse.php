<?php

namespace GlobalPayments\Api\Entities;

/**
 * BNPL response entity
 */
class BNPLResponse
{
    /** @var string */
    public $providerName;

    /**
     * URL to redirect the customer, sent so merchant can redirect consumer to complete the payment.
     *
     * @var string
     */
    public $redirectUrl;
}