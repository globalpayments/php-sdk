<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Builders\BaseBuilder\Validations;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Transaction;

abstract class TransactionBuilder extends BaseBuilder
{
    /**
     * Request transaction type
     *
     * @internal
     * @var TransactionType
     */
    public $transactionType;

    /**
     * Request payment method
     *
     * @internal
     * @var IPaymentMethod
     */
    public $paymentMethod;

    /**
     * Request transaction modifier
     *
     * @internal
     * @var TransactionModifier
     */
    public $transactionModifier = TransactionModifier::NONE;

    /**
     * Request should allow duplicates
     *
     * @internal
     * @var bool
     */
    public $allowDuplicates;

    /**
     * Instantiates a new builder
     *
     * @param TransactionType $type Request transaction type
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return
     */
    public function __construct($type, $paymentMethod = null)
    {
        parent::__construct();
        $this->transactionType = $type;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Set the request transaction type
     *
     * @internal
     * @param TransactionType $transactionType Request transaction type
     *
     * @return AuthorizationBuilder
     */
    public function withTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * Set the request transaction modifier
     *
     * @internal
     * @param TransactionModifier $modifier Request transaction modifier
     *
     * @return AuthorizationBuilder
     */
    public function withModifier($modifier)
    {
        $this->transactionModifier = $modifier;
        return $this;
    }

    /**
     * Set the request to allow duplicates
     *
     * @param bool $allowDuplicates Request to allow duplicates
     *
     * @return AuthorizationBuilder
     */
    public function withAllowDuplicates($allowDuplicates)
    {
        $this->allowDuplicates = $allowDuplicates;
        return $this;
    }
}
