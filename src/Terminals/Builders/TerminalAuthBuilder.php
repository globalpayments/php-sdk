<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Terminals\Entities\HostData;
use GlobalPayments\Api\Terminals\Enums\AcquisitionType;
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    StoredCredentialInitiator,
    TaxType,
    TransactionModifier,
    TransactionType
};
use GlobalPayments\Api\PaymentMethods\{CreditCardData, TransactionReference};
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Terminals\TerminalResponse;

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
    
    public $tokenRequest;
    
    public $tokenValue;
    
    /**
     * 
     * @var AutoSubstantiation
     */
    public $autoSubstantiation;

    public ?string $terminalRefNumber;

    public LodgingData $lodgingData;

    public \DateTime $shippingDate;

    public int $processCPC;

    public bool $confirmAmount;

    /**
     * 
     * @var bool
     */
    public $allowPartialAuth;

    /**
     * 
     * @var StoredCredentialInitiator
     */
    public $transactionInitiator;

    public bool $isQuickChip;
    public ?bool $hasCheckLuhn = null;
    public ?bool $hasSecurityCode = null;
    public array $cardTypeFilter = [];
    public \DateTime $transactionDate;
    /** @var int Number of seconds for timeout */
    public ?int $timeout;
    /** @var array<AcquisitionType> */
    public array $acquisitionTypes;
    public bool $displayTotalAmount;
    /** @var string|PromptForManualEntryPassword  */
    public ?string $promptForManualEntryPassword;
    public ?string $merchantDecision;
    public ?string $language;
    public HostData $hostData;

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
     * @param string $configName
     * @return TerminalResponse
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function execute(string $configName = "default") : TerminalResponse
    {
        parent::execute($configName);
        $client = ServicesContainer::instance()->getDeviceController($configName);

        return $client->processTransaction($this);
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
     * @return $this
     */
    public function withTransactionId(string $transactionId)
    {
        if ($this->paymentMethod == null || !$this->paymentMethod instanceof TransactionReference) {
            $this->paymentMethod = new TransactionReference();
            $this->paymentMethod->transactionId = $transactionId;
        }
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * 
     * @param null|StoredCredentialInitiator|string $transactionInitiator 
     * @param string|null $cardBrandTransactionId 
     * @return TerminalAuthBuilder 
     */
    public function withCardBrandStorage(
        StoredCredentialInitiator|string $transactionInitiator = null,
        string $cardBrandTransactionId = null
    )
    {
        $this->cardBrandTransId = $cardBrandTransactionId;
        $this->transactionInitiator = $transactionInitiator;
        return $this;
    }
  
    public function withEcrId(string $ecrId)
    {
        $this->ecrId = $ecrId;
        return $this;
    }

    public function withTerminalRefNumber(string $terminalRefNumber)
    {
        $this->terminalRefNumber = $terminalRefNumber;
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
    
    /**
     * 
     * @param AutoSubstantiation $healthCareCardData 
     * @return $this 
     */
    public function withAutoSubstantiation(AutoSubstantiation $healthCareCardData)
    {
        $this->autoSubstantiation = $healthCareCardData;
        return $this;
    }

    /**
     * 
     * @param bool $value 
     * @return $this 
     */
    public function withAllowPartialAuth(bool $value)
    {
        $this->allowPartialAuth = $value;
        return $this;
    }

    public function withLodgingData(LodgingData $lodgingData)
    {
        $this->lodgingData = $lodgingData;
        return $this;
    }

    public function withShippingDate(\DateTime $date)
    {
        $this->shippingDate = $date;
        return $this;
    }

    public function withProcessCPC(bool $processCPC)
    {
        $this->processCPC = (int) $processCPC;
        return $this;
    }

    public function withConfirmationAmount(bool $value) : TerminalAuthBuilder
    {
        $this->confirmAmount = $value;
        return $this;
    }

    public function withQuickChip(bool $value) : TerminalAuthBuilder
    {
        $this->isQuickChip = $value;
        return $this;
    }

    public function withCheckLuhn(bool $value) : TerminalAuthBuilder
    {
        $this->hasCheckLuhn = $value;
        return $this;
    }

    public function withSecurityCode(bool $value) : TerminalAuthBuilder
    {
        $this->hasSecurityCode = $value;
        return $this;
    }

    public function withCardTypeFilter(array $cardTypeFilter) : TerminalAuthBuilder
    {
        $this->cardTypeFilter = $cardTypeFilter;
        return $this;
    }

    public function withTransactionDate(\DateTime $date) : TerminalAuthBuilder
    {
        $this->transactionDate = $date;
        return $this;
    }

    public function withTimeout(int $timeout) : TerminalAuthBuilder
    {
        $this->timeout = $timeout;
        return $this;
    }
    public function withAcquisitionTypes(array $acquisitionTypes) : TerminalAuthBuilder
    {
        $this->acquisitionTypes = $acquisitionTypes;
        return $this;
    }

    public function withDisplayTotalAmount(bool $value) : TerminalAuthBuilder
    {
        $this->displayTotalAmount = $value;
        return $this;
    }

    public function withPromptForManualEntryPassword(string $value) : TerminalAuthBuilder
    {
        $this->promptForManualEntryPassword = $value;
        return $this;
    }

    public function withMerchantDecision(string $merchantDecision) : TerminalAuthBuilder
    {
        $this->merchantDecision = $merchantDecision;
        return $this;
    }

    public function withLanguage(string $language) : TerminalAuthBuilder
    {
        $this->language = $language;
        return $this;
    }

    public function withHostData(HostData $hostData) : TerminalAuthBuilder
    {
        $this->hostData = $hostData;
        return $this;
    }
}
