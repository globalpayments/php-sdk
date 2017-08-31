<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IReversable
{
    public function reverse($amount = null);
}
