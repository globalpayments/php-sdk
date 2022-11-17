<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class PersonList extends \ArrayObject
{
    public function append($value) : void
    {
        if (!$value instanceof Person) {
            throw new ArgumentException("Invalid argument type");
        }
        parent::append($value);
    }
}