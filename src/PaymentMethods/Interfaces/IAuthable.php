<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IAuthable
{
    public function authorize($amount = null);
}
