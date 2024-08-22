<?php

namespace GlobalPayments\Api\Terminals\Entities;

class PromptButtons
{
    private array $list;

    public function __construct(Button ...$button)
    {
        $this->list = $button;
    }

    public function add(Button $button) : void
    {
        $this->list[] = $button;
    }

    public function all() : array
    {
        return $this->list;
    }
}