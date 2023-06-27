<?php

namespace GlobalPayments\Api\Entities\PayFac;

class DeviceAttributeInfo
{
    /**
     * Name of attribute item which is specific to Portico devices for AMD.
     * The value of this item is passed to Heartland for
     * equipment boarding.
     *
     * @var string
     */
    public $name;

    /**
     * Value of the attribute item.
     *
     * @var string
     */
    public $value;
}
