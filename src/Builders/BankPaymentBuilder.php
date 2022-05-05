<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\ServicesContainer;

class BankPaymentBuilder extends BaseBuilder
{
    /** @var string|float */
    public $amount;

    /** @var IPaymentMethod */
    public $paymentMethod;

    /** @var string */
    public $currency;

    /** @var TransactionType */
    public $transactionType;

    /** @var TransactionModifier */
    public $transactionModifier = TransactionModifier::NONE;

    /** @var string */
    public $description;

    /** @var string */
    public $orderId;

    /** @var string */
    public $timestamp;

    /** @var string */
    public $remittanceReferenceValue;

    /** @var RemittanceReferenceType */
    public $remittanceReferenceType;

    public function __construct($transactionType, IPaymentMethod $paymentMethod = null)
    {
        parent::__construct();
        $this->transactionType = $transactionType;
        if (!is_null($paymentMethod)) {
            $this->withPaymentMethod($paymentMethod);
        }
    }

    public function setupValidations()
    {
        $this->validations->of(TransactionType::SALE)
            ->check('paymentMethod')->isNotNull()
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull();
    }

    public function execute($configName = 'default')
    {
        parent::execute();

        $client = ServicesContainer::instance()->getOpenBanking($configName);
        return $client->processOpenBanking($this);
    }

    public function serialize($configName = 'default')
    {
        $this->transactionModifier = TransactionModifier::HOSTEDREQUEST;
        parent::execute();

        $client = ServicesContainer::instance()->getOpenBanking($configName);

        if ($client->supportsHostedPayments()) {
            return $client->serializeRequest($this);
        }

        throw new UnsupportedTransactionException("Your current gateway does not support hosted payments.");
    }

    public function withCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function withDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function withPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function withOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function withModifier($transactionModifier)
    {
        $this->transactionModifier = $transactionModifier;
        return $this;
    }

    /**
     * @param string $timestamp
     *
     * @return $this
     */
    public function withTimeStamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function withRemittanceReference($remittanceReferenceType, $remittanceReferenceValue)
    {
        $this->remittanceReferenceType = $remittanceReferenceType;
        $this->remittanceReferenceValue = $remittanceReferenceValue;

        return $this;
    }
}