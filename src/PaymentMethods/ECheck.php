<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IChargable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class ECheck implements
    IPaymentMethod,
    IChargable
{
    public $accountNumber;
    public $accountType;
    public $achVerify;
    public $birthYear;
    public $checkHolderName;
    public $checkNumber;
    public $checkType;
    public $checkVerify;
    public $driversLicenseNumber;
    public $driversLicenseState;
    public $entryMode;
    public $micrNumber;
    public $paymentMethodType = PaymentMethodType::ACH;
    public $phoneNumber;
    public $routingNumber;
    public $secCode;
    public $ssnLast4;
    public $token;

    /**
     * Authorizes the payment method and captures the entire authorized amount
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function charge($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::SALE, $this))
            ->withAmount($amount);
    }
}
