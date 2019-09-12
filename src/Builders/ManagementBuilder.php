<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\ServicesContainer;

/**
 * @property string $transactionId
 */
class ManagementBuilder extends TransactionBuilder
{
    /**
     * Request amount
     *
     * @internal
     * @var string|float
     */
    public $amount;

    /**
     * Request authorization amount
     *
     * @internal
     * @var string|float
     */
    public $authAmount;

    /**
     * @internal
     * @var string
     */
    public $clientTransactionId;

    /**
     * Request currency
     *
     * @internal
     * @var string
     */
    public $currency;

    /**
     * @internal
     * @var string
     */
    public $description;

    /**
     * Request gratuity
     *
     * @internal
     * @var string|float
     */
    public $gratuity;

    /**
     * Request purchase order number
     *
     * @internal
     * @var string|float
     */
    public $poNumber;

    /**
     * @internal
     * @var ReasonCode
     */
    public $reasonCode;

    /**
     * Request tax amount
     *
     * @internal
     * @var string|float
     */
    public $taxAmount;

    /**
     * Request tax type
     *
     * @internal
     * @var TaxType
     */
    public $taxType;

    /**
     * Previous request's transaction reference
     *
     * @internal
     * @var IPaymentMethod
     */
    public $paymentMethod;
    
    /**
     * Previous request's transaction reference
     *
     * @internal
     * @var string
     */
    public $alternativePaymentType;

    /**
     * @internal
     * @var string
     */
    public $payerAuthenticationResponse;

    /**
     * {@inheritdoc}
     *
     * @param TransactionType $type Request transaction type
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return
     */
    public function __construct($type, $paymentMethod = null)
    {
        parent::__construct($type, $paymentMethod);
    }

    /**
     * Magic method for returning virtual properties
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'transactionId':
                if ($this->paymentMethod instanceof TransactionReference) {
                    return $this->paymentMethod->transactionId;
                }
                return null;
            case 'orderId':
                if ($this->paymentMethod instanceof TransactionReference) {
                    return $this->paymentMethod->orderId;
                }
                return null;
            case 'authorizationCode':
                if ($this->paymentMethod instanceof TransactionReference) {
                    return $this->paymentMethod->authCode;
                }
                return null;
        }
    }

    public function __isset($name)
    {
        return in_array($name, [
            'transactionId',
            'orderId',
            'authorizationId',
        ]) || isset($this->{$name});
    }

    /**
     * {@inheritdoc}
     *
     * @return Transaction
     */
    public function execute()
    {
        parent::execute();
        return ServicesContainer::instance()
            ->getClient()
            ->manageTransaction($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function setupValidations()
    {
        $this->validations->of(
            TransactionType::CAPTURE |
            TransactionType::EDIT |
            TransactionType::HOLD |
            TransactionType::RELEASE
        )
            ->check('transactionId')->isNotNull();

        $this->validations->of(TransactionType::EDIT)
            ->with(TransactionModifier::LEVEL_II)
            ->check('taxType')->isNotNull();

        $this->validations->of(TransactionType::REFUND)
            ->when('amount')->isNotNull()
            ->check('currency')->isNotNull();

        $this->validations->of(TransactionType::VERIFY_SIGNATURE)
            ->check('payerAuthenticationResponse')->isNotNull()
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull()
            ->check('orderId')->isNotNull();

        $this->validations->of(TransactionType::TOKEN_DELETE | TransactionType::TOKEN_UPDATE)
            ->check('paymentMethod')->isNotNull()
            ->check('paymentMethod')->isInstanceOf(ITokenizable::class);

        $this->validations->of(TransactionType::TOKEN_UPDATE)
            ->check('paymentMethod')->isInstanceOf(CreditCardData::class);
    }

    /**
     * Sets the current transaction's amount.
     *
     * @param string|float $amount The amount
     *
     * @return ManagementBuilder
     */
    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Sets the current transaction's authorized amount; where applicable.
     *
     * @param string|float $authAmount The authorized amount
     *
     * @return ManagementBuilder
     */
    public function withAuthAmount($authAmount)
    {
        $this->authAmount = $authAmount;
        return $this;
    }

    /**
     * Sets the currency.
     *
     * The formatting for the supplied value will currently depend on the
     * configured gateway's requirements.
     *
     * @param string $currency The currency
     *
     * @return ManagementBuilder
     */
    public function withCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Sets the transaction's description.
     *
     * This value is not guaranteed to be sent in the authorization
     * or settlement process.
     *
     * @param string $value The description
     *
     * @return ManagementBuilder
     */
    public function withDescription($value)
    {
        $this->description = $value;
        return $this;
    }

    /**
     * Sets the gratuity amount; where applicable.
     *
     * This value is information only and does not affect the
     * authorization amount.
     *
     * @param string|float $gratuity the gratuity
     *
     * @return ManagementBuilder
     */
    public function withGratuity($gratuity)
    {
        $this->gratuity = $gratuity;
        return $this;
    }

    /**
     * @return ManagementBuilder
     */
    public function withIssuerData(CardIssuerEntryTag $tag, String $value)
    {
        if ($this->issuerData == null) {
            $this->issuerData = [];
        }
        $this->issuerData[$tag] = $value;
        return $this;
    }

    /**
     * Previous request's transaction reference
     *
     * @internal
     * @param IPaymentMethod $paymentMethod Transaction reference
     *
     * @return ManagementBuilder
     */
    public function withPaymentMethod(IPaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * Sets the purchase order number; where applicable.
     *
     * @param string|float $poNumber The purchase order number
     *
     * @return ManagementBuilder
     */
    public function withPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;
        return $this;
    }

    /**
     * Sets the reason code for the transaction.
     *
     * @param ReasonCode $value The reason code
     *
     * @return ManagementBuilder
     */
    public function withReasonCode($value)
    {
        $this->reasonCode = $value;
        return $this;
    }

    /**
     * Sets the tax amount.
     *
     * Useful for commercial purchase card requests.
     *
     * @param string|float $taxAmount The tax amount
     *
     * @return ManagementBuilder
     */
    public function withTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * Sets the tax type.
     *
     * Useful for commercial purchase card requests.
     *
     * @param TaxType $taxType The tax type
     *
     * @return ManagementBuilder
     */
    public function withTaxType($taxType)
    {
        $this->taxType = $taxType;
        return $this;
    }
    
    public function withAlternativePaymentType($alternativePaymentType)
    {
        $this->alternativePaymentType = $alternativePaymentType;
        return $this;
    }

    public function withPayerAuthenticationResponse($payerAuthenticationResponse)
    {
        $this->payerAuthenticationResponse = $payerAuthenticationResponse;
        return $this;
    }
}
