<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IChargable
{
    public function charge($amount = null);
}
