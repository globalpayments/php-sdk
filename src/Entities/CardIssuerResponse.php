<?php

namespace GlobalPayments\Api\Entities;

/**
 * The result codes directly from the card issuer.
 */
class CardIssuerResponse
{
    /**
     *The result code of the AVS check from the card issuer.
     *
     * @var string
     */
    public $avsResult;

    /**
     * Result code from the card issuer.
     *
     * @var string
     */
    public $result;

    /**
     * The result code of the CVV check from the card issuer.
     *
     * @var string
     */
    public $cvvResult;

    /**
     * The result code of the AVS address check from the card issuer.
     *
     * @var string
     */
    public $avsAddressResult;

    /**
     * The result of the AVS postal code check from the card issuer.
     *
     * @var string
     */
    public $avsPostalCodeResult;
}