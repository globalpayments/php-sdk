<?php

namespace GlobalPayments\Api\Terminals\Entities;

class PANDetails
{
    /** @var string|null Unencrypted PAN. */
    public ?string $clearPAN;
    /** @var string|null Masked PAN. First 6 digits and last 4 digits of the PAN entered are returned in the clear. The middle
    digits are scrambled. */
    public ?string $maskedPAN;
    /** @var string|null Encrypted PAN which is converted to base64 */
    public ?string $encryptedPAN;
}