<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

class OpenTab
{
    public ?string $authorizedAmount;
    public ?string $cardType;
    public ?string $maskedPan;
    public ?string $transactionId;
    public ?string $clerkId;
}