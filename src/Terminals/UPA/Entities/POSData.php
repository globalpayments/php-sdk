<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

class POSData
{
    public string $appName;
    public ?int $launchOrder;
    public ?bool $remove;
    public ?int $silent;
}