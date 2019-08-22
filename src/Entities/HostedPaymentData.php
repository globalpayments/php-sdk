<?php

namespace GlobalPayments\Api\Entities;

/**
 * Data collection to supplement a hosted payment page.
 */
class HostedPaymentData
{
    /**
     * Indicates to the issuer that the shipping and billing
     * addresses are expected to be the same. Used as a fraud
     * prevention mechanism.
     *
     * @var boolean
     */
    public $addressesMatch;

    /**
     * Determines the challenge request preference for 3DS 2.0.
     *
     * @var ChallengeRequestIndicator
     */
    public $challengeRequest;

    /**
     * The customer's email address.
     *
     * @var string
     */
    public $customerEmail;

    /**
     * Indicates if the customer is known and has an account.
     *
     * @var boolean
     */
    public $customerExists;

    /**
     * The identifier for the customer.
     *
     * @var string
     */
    public $customerKey;

    /**
     * The customer's number.
     *
     * @var string
     */
    public $customerNumber;

    /**
     * The customer's mobile phone number.
     *
     * @var string
     */
    public $customerPhoneMobile;

    /**
     * Indicates if the customer should be prompted to store their card.
     *
     * @var boolean
     */
    public $offerToSaveCard;

    /**
     * The identifier for the customer's desired payment method.
     *
     * @var string
     */
    public $paymentKey;

    /**
     * The product ID.
     *
     * @var string
     */
    public $productId;

    /**
     * Supplementary data that can be supplied at the descretion
     * of the merchant/application.
     *
     * @var array<string,string>
     */
    public $supplementaryData;

    /**
     * Instantiates a new `HostedPaymentData` object.
     *
     * @return
     */
    public function __construct()
    {
        $this->supplementaryData = [];
    }
}
