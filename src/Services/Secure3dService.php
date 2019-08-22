<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ISecure3d;

class Secure3dService
{
    /** @return Secure3dBuilder */
    public static function checkEnrollment(IPaymentMethod $paymentMethod)
    {
        return (new Secure3dBuilder(TransactionType::VERIFY_ENROLLED))
            ->withPaymentMethod($paymentMethod);
    }

    /** @return Secure3dBuilder */
    public static function initiateAuthentication(IPaymentMethod $paymentMethod, ThreeDSecure $secureEcom)
    {
        $paymentMethod->threeDSecure = $secureEcom;
        
        return (new Secure3dBuilder(TransactionType::INITIATE_AUTHENTICATION))
            ->withPaymentMethod($paymentMethod);
    }

    /** @return Secure3dBuilder */
    public static function getAuthenticationData()
    {
        return new Secure3dBuilder(TransactionType::VERIFY_SIGNATURE);
    }
}
