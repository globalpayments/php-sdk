<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Entities\Enums\{TaxType, TransactionModifier, TransactionType};
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\TerminalResponse;

class TerminalManageBuilder extends TerminalBuilder
{
    public $amount;
    public $currency;
    public $gratuity;
    public $transactionId;
    public $terminalRefNumber;
    public string $taxType;

    /** @var string Indicates whether the sale is exempted from Tax or not */
    public string $taxExempt;

    /** @var string Purchase Order to be sent to the host */
    public string $orderId;

    /** @var string The amount that merchants charge for tax processing.  */
    public string $taxAmount;

    public LodgingData $lodgingData;

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
     * @param string $configName
     * @return TerminalResponse
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function execute($configName = "default") : TerminalResponse
    {
        parent::execute();
        $client = ServicesContainer::instance()->getDeviceController($configName);
        return $client->manageTransaction($this);
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
     * @return TerminalManageBuilder
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

    public function withTerminalRefNumber($terminalRefNumber)
    {
        $this->terminalRefNumber = $terminalRefNumber;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return TerminalManageBuilder
     */
    public function withClientTransactionId(string $value) : TerminalManageBuilder
    {
        $this->clientTransactionId = $value;
        return $this;
    }

    public function withTransactionModifier(string $modifier) : TerminalManageBuilder
    {
        $this->transactionModifier = $modifier;
        return $this;
    }

    public function withEcrId(string $ecrId) : TerminalManageBuilder
    {
        $this->ecrId = $ecrId;
        return $this;
    }

    public function withTaxType($taxType)
    {
        $this->taxType = $taxType;
        $this->taxExempt = ($taxType === TaxType::TAX_EXEMPT) ? 1 : 0;

        return $this;
    }

    public function withOrderId(string $orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function withTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function withLodgingData(LodgingData $lodgingData)
    {
        $this->lodgingData = $lodgingData;
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

        $this->validations->of(
            TransactionType::REFUND
        )
            ->check('transactionId')->isNotNull();

        $this->validations->of(
            TransactionType::AUTH
        )
            ->with(TransactionModifier::INCREMENTAL)
            ->check('transactionId')->isNotNull();
    }
}
