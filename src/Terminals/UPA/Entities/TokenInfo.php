<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

class TokenInfo
{
    /**
     * Gets or sets the token value.
     *
     * @var string
     */
    public ?string $token = null;

    /**
     * Gets or sets the expiry month of the token (MM format).
     *
     * @var string
     */
    public ?string $expiryMonth = null;

    /**
     * Gets or sets the expiry year of the token (YYYY format).
     *
     * @var string
     */
    public ?string $expiryYear = null;
}
