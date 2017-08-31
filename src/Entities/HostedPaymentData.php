<?php

namespace GlobalPayments\Api\Entities;

/**
 * Data collection to supplement a hosted payment page.
 */
class HostedPaymentData
{
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
