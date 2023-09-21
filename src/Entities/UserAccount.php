<?php

namespace GlobalPayments\Api\Entities;

class UserAccount
{
    public string $id;
    public ?string $name;
    public ?string $type;

    public function __construct(string $id, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }
}