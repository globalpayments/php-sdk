<?php

namespace GlobalPayments\Api\Entities;

/**
 * A payer resource for managing customer payment information
 */
class Payer
{
    /**
     * Unique identifier for the payer, will allway start with PYR_
     *
     * @var string|null
     */
    public ?string $id = null;

    /**
     * Merchant reference for the payer
     *
     * @var string|null
     */
    public ?string $reference = null;

    /**
     * Payer's first name
     *
     * @var string|null
     */
    public ?string $firstName = null;

    /**
     * Payer's last name
     *
     * @var string|null
     */
    public ?string $lastName = null;

    /**
     * Payer's email address
     *
     * @var string|null
     */
    public ?string $email = null;


    /**
     * Payer's language
     *
     * @var string|null
     */
    public ?string $language = null;


    /**
     * Payment methods associated with this payer
     *
     * @var array|null
     */
    public ?array $paymentMethods = null;
}
