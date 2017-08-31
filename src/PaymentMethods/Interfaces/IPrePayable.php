<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IPrePayable
{
    public function addValue($amount = null);
}
