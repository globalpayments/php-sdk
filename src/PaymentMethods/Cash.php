<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\IChargable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IRefundable;

class Cash implements
    IPaymentMethod,
    IChargable,
    IRefundable
{
    public $paymentMethodType = PaymentMethodType::CASH;

    public function charge($amount = null)
    {
        throw new NotImplementedException();
    }

    public function refund($amount = null)
    {
        throw new NotImplementedException();
    }
}
