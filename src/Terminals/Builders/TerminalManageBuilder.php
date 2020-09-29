<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\ConnectionContainer;
use GlobalPayments\Api\PaymentMethods\TransactionReference;

class TerminalManageBuilder extends TerminalBuilder
{
    public $amount;
    public $currency;
    public $gratuity;
    public $transactionId;

    /**
     * {@inheritdoc}
     *
     * @param TransactionType $transactionType Request transaction type
     * @param PaymentMethodType $paymentMethodType Request payment method
     *
     * @return
     */
    public function __construct($transactionType, $paymentMethodType = null)
    {
        parent::__construct($transactionType, $paymentMethodType);
        $this->transactionType = $transactionType;
        $this->paymentMethodType = $paymentMethodType;
    }

    /**
     * {@inheritdoc}
     *
     * @return Transaction
     */
    public function execute()
    {
        parent::execute();
        return ConnectionContainer::instance()
                        ->manageTransaction($this);
    }

    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function withCurrency($value)
    {
        $this->currency = $value;
        return $this;
    }

    public function withGratuity($gratuity)
    {
        $this->gratuity = $gratuity;
        return $this;
    }

    public function withPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }

    /**
     * Previous request's transaction ID
     *
     * @param string $transactionId Transaction ID
     *
     * @return AuthorizationBuilder
     */
    public function withTransactionId($transactionId)
    {
        if ($this->paymentMethod == null || !$this->paymentMethod instanceof TransactionReference) {
            $this->paymentMethod = new TransactionReference();
            $this->paymentMethod->transactionId = $transactionId;
        }
        $this->transactionId = $transactionId;
        return $this;
    }

    protected function setupValidations()
    {
        $this->validations->of(
            TransactionType::CAPTURE
        )
                ->with(TransactionModifier::NONE)
                ->check('amount')->isNotNull()
                ->check('transactionId')->isNotNull();
        
        $this->validations->of(
            TransactionType::VOID
        )
                ->with(TransactionModifier::NONE)
                ->check('transactionId')->isNotNull();
    }
}
