<?php

namespace GlobalPayments\Api\Entities;

class AddressCollection extends \ArrayObject
{
    /**
     * @param string $type
     * @param Address $address
     */
    public function add(Address $address, string $type)
    {
        $this->offsetSet($type, $address);
    }

    public function get(string $type) : Address
    {
        return $this->offsetGet($type);
    }
}