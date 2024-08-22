<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

class PinDUKPTResponse
{
    public ?string $pinBlock;
    /** @var string|null KSN of the “Pinblock”. */
    public ?string $ksn;
}