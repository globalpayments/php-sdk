<?php

namespace GlobalPayments\Api\Entities\PayFac;

use ArrayObject;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class DeviceDetails extends \ArrayObject
{
    public $quantity;
    public $timezone;
    public $name;
    public $attributes;

    public function __construct()
    {
        $this->attributes = new DeviceAttributeInfo();
    }

    /**
     * @param DeviceDetails $value
     *
     * @return void
     */
    public function append($value) : void
    {
        if (!$value instanceof DeviceDetails) {
            throw new ArgumentException("Invalid argument type");
        }
        parent::append($value);
    }
}
