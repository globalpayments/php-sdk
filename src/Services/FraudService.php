<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\FraudBuilder;
use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class FraudService
{
    /**
     * @param IPaymentMethod $paymentMethod
     * @return FraudBuilder
     */
    public static function riskAssess(IPaymentMethod $paymentMethod)
    {
        return (new FraudBuilder(TransactionType::RISK_ASSESS))
            ->withPaymentMethod($paymentMethod);
    }
}