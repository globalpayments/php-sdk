<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;

class CreditService
{
    /**
     * Instatiates a new object
     *
     * @param ServicesConfig $config Service config
     *
     * @return void
     */
    public function __construct(ServicesConfig $config)
    {
        ServicesContainer::configure($config);
    }

    /**
     * Creates an authorization builder with type
     * `TransactionType::CREDIT_AUTH`
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function authorize($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::AUTH))
            ->withAmount($amount);
    }

    /**
     * Creates a manage transaction builder with type `TransactionType::CAPTURE`
     *
     * @param string|float|TransactionReference $transaction Transaction reference of an authorization
     *
     * @return ManagementBuilder
     */
    public function capture($transaction = null)
    {
        if (!($transaction instanceof TransactionReference)) {
            $transactionReference = new TransactionReference();
            $transactionReference->transactionId = $transaction;
            $transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
            $transaction = $transactionReference;
        }

        return (new ManagementBuilder(TransactionType::CAPTURE))
            ->withPaymentMethod($transaction);
    }

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

    public function edit($transaction = null)
    {
        if (!($transaction instanceof TransactionReference)) {
            $transactionReference = new TransactionReference();
            $transactionReference->transactionId = $transaction;
            $transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
            $transaction = $transactionReference;
        }

        return (new ManagementBuilder(TransactionType::EDIT))
            ->withPaymentMethod($transaction);
    }

    public function editLevelII($transaction = null)
    {
        if (!($transaction instanceof TransactionReference)) {
            $transactionReference = new TransactionReference();
            $transactionReference->transactionId = $transaction;
            $transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
            $transaction = $transactionReference;
        }

        return (new ManagementBuilder(TransactionType::EDIT))
            ->withModifier(TransactionModifier::LEVEL_II)
            ->withPaymentMethod($transaction);
    }

    /**
     * Refunds the payment method
     *
     * @param string|float $amount Amount to refund
     *
     * @return AuthorizationBuilder
     */
    public function refund($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REFUND, $this))
            ->withAmount($amount);
    }

    /**
     * Reverses the payment method
     *
     * @param string|float $amount Amount to reverse
     *
     * @return AuthorizationBuilder
     */
    public function reverse($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REVERSAL, $this))
            ->withAmount($amount);
    }

    /**
     * Verifies the payment method
     *
     * @return AuthorizationBuilder
     */
    public function verify()
    {
        return new AuthorizationBuilder(TransactionType::VERIFY, $this);
    }

    public function void($transaction = null)
    {
        if (!($transaction instanceof TransactionReference)) {
            $transactionReference = new TransactionReference();
            $transactionReference->transactionId = $transaction;
            $transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
            $transaction = $transactionReference;
        }

        return (new ManagementBuilder(TransactionType::VOID))
            ->withPaymentMethod($transaction);
    }
}
