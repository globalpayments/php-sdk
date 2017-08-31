<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IRefundable
{
    public function refund($amount = null);
}
