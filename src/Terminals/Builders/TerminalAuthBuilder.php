<?php
namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\ConnectionContainer;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Entities\Enums\TaxType;

class TerminalAuthBuilder extends TerminalBuilder
{

    public $address;

    public $allowDuplicates;

    public $amount;

    public $authCode;

    public $cashBackAmount;

    public $currency;

    public $customerCode;

    public $gratuity;

    public $invoiceNumber;

    public $poNumber;

    public $requestMultiUseToken;

    public $signatureCapture;

    public $taxAmount;

    public $taxExempt;

    public $taxExemptId;

    public $transactionId;

    public $shiftId;

    public $taxType;
    
    public $clientTransactionId;
    
    public $tokenRequest;
    
    public $tokenValue;
    
    public $autoSubstantiation;

    /**
     *
     * {@inheritdoc}
     *
     * @param TransactionType $transactionType
     *            Request transaction type
     * @param PaymentMethodType $paymentMethodType
     *            Request payment method
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
     *
     * {@inheritdoc}
     *
     * @return Transaction
     */
    public function execute()
    {
        parent::execute();
        return ConnectionContainer::instance()->processTransaction($this);
    }

    public function withAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function withAllowDuplicates($allowDuplicates)
    {
        $this->allowDuplicates = $allowDuplicates;
        return $this;
    }

    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function withCashBack($amount)
    {
        $this->cashBackAmount = $amount;
        return $this;
    }

    public function withCurrency($value)
    {
        $this->currency = $value;
        return $this;
    }

    public function withCustomerCode($customerCode)
    {
        $this->customerCode = $customerCode;
        return $this;
    }

    public function withGratuity($gratuity)
    {
        $this->gratuity = $gratuity;
        return $this;
    }

    public function withInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function withPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }

    public function withPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;
        return $this;
    }

    public function withRequestMultiUseToken($requestMultiUseToken)
    {
        $this->requestMultiUseToken = $requestMultiUseToken;
        return $this;
    }

    public function withSignatureCapture($signatureCapture)
    {
        $this->signatureCapture = $signatureCapture;
        return $this;
    }

    public function withTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function withToken($value)
    {
        if ($this->paymentMethod == null || !($this->paymentMethod instanceof CreditCardData)) {
            $this->paymentMethod = new CreditCardData();
            $this->paymentMethod->token = $value;
        }
        return $this;
    }

    /**
     * Previous request's transaction ID
     *
     * @param string $transactionId
     *            Transaction ID
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
        $this->validations->of(TransactionType::AUTH | TransactionType::SALE | TransactionType::REFUND)
            ->with(TransactionModifier::NONE)
            ->check('amount')
            ->isNotNull();

        $this->validations->of(TransactionType::REFUND)
            ->check('amount')
            ->isNotNull();

        $this->validations->of(TransactionType::REFUND)
            ->with(PaymentMethodType::CREDIT)
            ->check('transactionId')
            ->isNotNull()
            ->check('authCode')
            ->isNotNull();

        $this->validations->of(TransactionType::ADD_VALUE)
            ->check('amount')
            ->isNotNull();

        $this->validations->of(TransactionType::BALANCE)
            ->check("currency")
            ->isNotNull()
            ->check("currency")
            ->isNotEqualTo(CurrencyType::VOUCHER);

        $this->validations->of(TransactionType::BENEFIT_WITHDRAWAL)
            ->check("currency")
            ->isNotNull()
            ->check("currency")
            ->isEqualTo(CurrencyType::CASH_BENEFITS);

        $this->validations->of(TransactionType::REFUND)
            ->with(PaymentMethodType::EBT)
            ->check("allowDuplicates")
            ->isEqualTo(false);

        $this->validations->of(TransactionType::BENEFIT_WITHDRAWAL)
            ->with(PaymentMethodType::EBT)
            ->check("allowDuplicates")
            ->isEqualTo(false);
    }

    public function withTaxType($taxType, $taxExemptId = null)
    {
        $this->taxType = $taxType;
        $this->taxExempt = ($taxType === TaxType::TAX_EXEMPT) ? 1 : 0;
        $this->taxExemptId = $taxExemptId;
        return $this;
    }
    
    public function withClientTransactionId($clientTransactionId)
    {
        $this->clientTransactionId = $clientTransactionId;
        return $this;
    } 
    
    public function withAutoSubstantiation($healthCareCardData)
    {
        $this->autoSubstantiation = $healthCareCardData;
        return $this;
    }
}
