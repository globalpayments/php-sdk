<?php

namespace GlobalPayments\Api\Terminals\Entities;

class HostData
{
    /** @var string|HostData|null */
    public ?string $hostDecision = null;
    public ?string $issuerScripts = null;
    public ?string $issuerAuthData = null;
}