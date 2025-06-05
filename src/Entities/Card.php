<?php

namespace GlobalPayments\Api\Entities;

class Card
{
    /** @var string */
    public $cardHolderName;

    /** @var string */
    public $cardNumber;

    /** @var string */
    public $maskedCardNumber;

    /** @var string */
    public $cardExpMonth;

    /** @var string */
    public $cardExpYear;

    /** @var string */
    public $token;

    /**
     * Masked card number with last 4 digits showing.
     * @var string
     */
    public $maskedNumberLast4;

    /**
     * Indicates the card brand that issued the card.
     * @var string
     */
    public $brand;

    /**
     * The unique reference created by the brands/schemes to uniquely identify the transaction.
     * @var string
     */
    public $brandReference;

    /**
     * Contains the fist 6 digits of the card
     * @var string
     */
    public $bin;

    /**
     * The issuing country that the bin is associated with.
     * @var string
     */
    public $binCountry;

    /**
     * The card providers description of their card product.
     * @var string
     */
    public $accountType;

    /**
     * The label of the issuing bank or financial institution of the bin.
     * @var string
     */
    public $issuer;

    /** @var string|null The result of the CVV check. */
    public ?string $cvnResponseMessage;
    /** @var string|null The result of the AVS address check. */
    public ?string $avsAddressResponse;
    /** @var string|null The result of the AVS postal code check. */
    public ?string $avsResponseCode;
    public ?string $tagResponse;

    public ?string $funding;
    public ?string $authCode;
}