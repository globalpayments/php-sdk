<?php

namespace GlobalPayments\Api\Entities\GpApi;

class GpApiAccount
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}