<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class PaymentMethodList extends \ArrayObject
{
    public function append($value) : void
    {
        if (!isset($value['payment_method']) || !$value['payment_method'] instanceof IPaymentMethod) {
            throw new ArgumentException("Invalid argument type");
        }
        parent::append($value);
    }
}