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
    public ?string $accountName = null;

    /**
     * Financial institution account number.
     *
     * @var string
     */
    public ?string $accountNumber = null;

    /**
     * A  SORT   Code   is a number code, which is used by British and Irish banks.
     * These codes have six digits, and they are divided into three different pairs, such as 12-34-56.
     *
     * @var string
     */
    public ?string $sortCode = null;

    /**
     * The International Bank Account Number
     *
     * @var string
     */
    public ?string $iban = null;

    public mixed $paymentMethodType = PaymentMethodType::BANK_PAYMENT;

    /** @var string */
    public ?string $returnUrl = null;

    /** @var string */
    public ?string $statusUpdateUrl = null;

    /** @var BankPaymentType */
    public mixed $bankPaymentType = null;

    /** @var array */
    public ?array $countries = null;

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

    /** @return PaymentMethodType */
    function getPaymentMethodType()
    {
        return $this->paymentMethodType;
    }
}