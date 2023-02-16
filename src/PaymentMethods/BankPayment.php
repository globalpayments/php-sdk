<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\BankPaymentType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IChargable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class BankPayment implements IPaymentMethod, IChargable
{
    /**
     * Merchant/Individual Name.
     *
     * @var string
     */
    public $accountName;

    /**
     * Financial institution account number.
     *
     * @var string
     */
    public $accountNumber;

    /**
     * A  SORT   Code   is a number code, which is used by British and Irish banks.
     * These codes have six digits, and they are divided into three different pairs, such as 12-34-56.
     *
     * @var string
     */
    public $sortCode;

    /**
     * The International Bank Account Number
     *
     * @var string
     */
    public $iban;

    public $paymentMethodType = PaymentMethodType::BANK_PAYMENT;

    /** @var string */
    public $returnUrl;

    /** @var string */
    public $statusUpdateUrl;

    /** @var BankPaymentType */
    public $bankPaymentType;

    /** @var array */
    public $countries;

    /**
     * This is a mandatory request used to initiate an Open Banking transaction,
     *
     * @param string|float|null $amount Amount to charge
     *
     * @return AuthorizationBuilder
     */
    public function charge($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::SALE, $this))
            ->withModifier(TransactionModifier::BANK_PAYMENT)
            ->withAmount($amount);
    }
}