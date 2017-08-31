<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface IBalanceable
{
    public function balanceInquiry($inquiry = null);
}
