<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BankPaymentBuilder;
use GlobalPayments\Api\Entities\Enums\BankPaymentType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class BankPayment implements IPaymentMethod
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

    /**
     * This is a mandatory request used to initiate an Open Banking transaction,
     *
     * @param string|float $amount Amount to authorize
     *
     * @return BankPaymentBuilder
     */
    public function charge($amount)
    {
        return (new BankPaymentBuilder(TransactionType::SALE, $this))
            ->withModifier(TransactionModifier::BANK_PAYMENT)
            ->withAmount($amount);
    }
}