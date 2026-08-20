<?php

namespace GlobalPayments\Api\Entities\GpApi;

class GpApiAccount
{
    public ?string $id;
    public ?string $name;
    public ?array $permissions;

    public function __construct(?string $id, ?string $name, ?array $permissions = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->permissions = $permissions;
    }
}