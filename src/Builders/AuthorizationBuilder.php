<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\{Address,
    BlockedCardType,
    Customer,
    AutoSubstantiation,
    EcommerceInfo,
    FraudRuleCollection,
    HostedPaymentData,
    HPPData,
    PhoneNumber,
    StoredCredential,
    InstallmentData,
    OrderDetails,
    DccRateData,
    DecisionManager,
    Transaction};
use GlobalPayments\Api\Entities\BillPay\Bill;
use GlobalPayments\Api\Entities\Enums\{AddressType,
    AliasAction,
    BNPLShippingMethod,
    CreditDebitIndicator,
    EmvFallbackCondition,
    EmvLastChipRead,
    InquiryType,
    FraudFilterMode,
    MerchantCategory,
    PaymentMethodUsageMode,
    PhoneNumberType,
    RemittanceReferenceType,
    RecurringSequence,
    RecurringType,
    TransactionModifier,
    TransactionType};
use GlobalPayments\Api\PaymentMethods\{BankPayment, BNPL, EBTCardData, GiftCard, TransactionReference};
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\Entities\Exceptions\{ArgumentException,BuilderException};

class AuthorizationBuilder extends TransactionBuilder
{

    /**
     * Request alias
     *
     * @internal
     * @var string
     */
    public ?string $alias = null;

    /**
     * Request alias action
     *
     * @internal
     * @var AliasAction
     */
    public mixed $aliasAction = null;

    /**
     * Request should allow duplicates
     *
     * @internal
     * @var bool
     */
    public ?bool $allowDuplicates = null;

    /**
     * Request should allow partial authorizations
     *
     * @internal
     * @var bool
     */
    public ?bool $allowPartialAuth = null;

    /**
     * Request amount
     *
     * @internal
     * @var string|float
     */
    public string|float|null $amount = null;

    /** @var bool */
    public ?bool $amountEstimated = null;

    /**
     * Request authorization amount
     *
     * @internal
     * @var string|float
     */
    public string|float|null $authAmount = null;

    /** @var AutoSubstantiation */
    public ?AutoSubstantiation $autoSubstantiation = null;

    /**
     * Balance inquiry type
     * @internal
     * @var InquiryType
     */
    public mixed $balanceInquiryType = null;

    /**
     * Request billing address
     *
     * @internal
     * @var Address
     */
    public ?Address $billingAddress = null;

    /**
     * Indicates Card On File transaction
     *
     * @internal
     * @var bool
     */
    public ?bool $cardOnFile = null;

    /**
     * Request cashback amount
     *
     * @internal
     * @var string|float
     */
    public string|float|null $cashBackAmount = null;

    /**
     * Request commercial data
     *
     * @internal
     * @var CommercialData
     */
    public mixed $commercialData = null;

    /**
     * Request currency
     *
     * @internal
     * @var string
     */
    public ?string $currency = null;

    /**
     * Request customer ID
     *
     * @internal
     * @var string|float
     */
    public string|float|null $customerId = null;

    /**
     * Request customer IP address
     *
     * @internal
     * @var string|float
     */
    public string|float|null $customerIpAddress = null;

    /**
     * Request customer Data
     *
     * @internal
     * @var Customer
     */
    public ?Customer $customerData = null;

    /**
     * Request customData
     *
     * @internal
     * @var array<string>
     */
    public array|string|null $customData = null;

    /**
     * Payment method CVN
     *
     * Only applicable for recurring payments
     *
     * @internal
     * @var string
     */
    public ?string $cvn = null;

    /**
     * Request description
     *
     * @internal
     * @var string
     */
    public ?string $description = null;

    /**
     * Request decisionManager
     *
     * @internal
     * @var DecisionManager
     */
    public ?DecisionManager $decisionManager = null;

    /**
     * Request dynamic descriptor
     *
     * @internal
     * @var string
     */
    public ?string $dynamicDescriptor = null;

    /**
     * Request ecommerceInfo
     *
     * @internal
     * @var EcommerceInfo
     */
    public ?EcommerceInfo $ecommerceInfo = null;

    /**
     * Request gratuity
     *
     * @internal
     * @var string|amount
     */
    public string|float|null $gratuity = null;

    /**
     * @var ?float
     */
    public ?float $convenienceAmount = null;

    /**
     * Request shipping amount
     *
     * @internal
     * @var string|amount
     */
    public string|float|null $shippingAmount = null;

    /** @var string|float */
    public string|float|null $shippingDiscount = null;

    /** @var OrderDetails */
    public ?OrderDetails $orderDetails = null;

    /**
     * @internal
     * @var StoredCredential
     */
    public ?StoredCredential $storedCredential = null;

    /**
     * @internal
     * @var InstallmentData
     */
    public ?InstallmentData $installment = null;

    /**
     * Request hosted payment data
     *
     * @internal
     * @var HostedPaymentData
     */
    public mixed $hostedPaymentData = null;

    /**
     * Request invoice number
     *
     * @internal
     * @var string|float
     */
    public string|float|null $invoiceNumber = null;

    /**
     * Request contract reference
     *
     * @internal
     * @var string
     */
    public ?string $contractReference = null;

    /**
     * Request should request Level II
     *
     * @internal
     * @var bool
     */
    public ?bool $level2Request = null;

    /**
     * Request offline authorization code
     *
     * @internal
     * @var string
     */
    public ?string $offlineAuthCode = null;

    /**
     * Request should be considered one-time
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var bool
     */
    public ?bool $oneTimePayment = null;

    /**
     * Request order ID
     *
     * @internal
     * @var string|float
     */
    public string|float|null $orderId = null;

    /**
     * Request product Data
     *
     * @internal
     * @var array<string>
     */
    public ?array $productData = null;

    /**
     * Request product ID
     *
     * @internal
     * @var string|float
     */
    public string|float|null $productId = null;

    /**
     * Request recurring sequence
     *
     * @internal
     * @var RecurringSequence
     */
    public mixed $recurringSequence = null;

    /**
     * Request recurring type
     *
     * @internal
     * @var RecurringType
     */
    public mixed $recurringType = null;

    /**
     * Request should request multi-use token
     *
     * @internal
     * @var bool
     */
    public ?bool $requestMultiUseToken = null;

    /**
     * Used in conjunction with $requestMultiUseToken to request a unique token
     * For use with Portico Gateway only
     *
     * @internal
     * @var bool
     */
    public bool $requestUniqueToken;

    /**
     * To attach registration most recent change date value
     * For use w/Discover cards on TransIT gateway
     *
     * @internal
     * @var Date
     */
    public mixed $lastRegisteredDate = null;

    /**
     * Request replacement gift card
     *
     * @internal
     * @var GiftCard
     */
    public ?GiftCard $replacementCard = null;

    /**
     * Request schedule ID
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var string
     */
    public ?string $scheduleId = null;

    /**
     * Request shipping address
     *
     * @internal
     * @var Address
     */
    public ?Address $shippingAddress = null;

    /**
     * Request timestamp
     *
     * @internal
     * @var string|float
     */
    public string|float|null $timestamp = null;

    /**
     * DCC rate Data
     *
     * @internal
     * @var dccRateData
     */
    public ?DccRateData $dccRateData = null;

    /**
     * Fraud Filter
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var string
     */
    public mixed $fraudFilter = null;

    /**
     * @var FraudRuleCollection
     */
    public mixed $fraudRules = null;

    /**
     * For AVS (Address verification System) request
     *
     * @internal
     * @var bool
     */
    public string|bool|null $verifyAddress = null;

    /**
     * For TransIT cash amount for a specified transaction
     * Note: If a decimal point is included, the amount reflects a dollar value.
     *       If a decimal point is not included, the amount reflects a cent value.
     *
     * @internal
     * @var string
     */
    public ?string $cashTendered = null;

    /**
     * For TransIT transaction discount details
     *
     * @internal
     * @var string
     */
    public ?string $discountDetails = null;

    /*
     * Card on File field
     * @var string
     *
     */
    public ?string $cardBrandTransactionId = null;

    /*
     * Card on File field
     * @var string
     *
     */
    public ?string $transactionInitiator = null;

    /**
     * Used with some stored-credential transactions
     *
     * @var string $categoryIndicator
     */
    public string $categoryIndicator;

    /**
     * @var string $tagData
     */
    public ?string $tagData = null;

    /**
     * @var string $idempotencyKey
     */
    public ?string $idempotencyKey = null;

    /**
     * @var EmvLastChipRead $emvLastChipRead
     */
    public ?EmvLastChipRead $emvLastChipRead = null;

    /**
     * @var string $paymentApplicationVersion
     */
    public ?string $paymentApplicationVersion = null;

    /**
     * @var EmvFallbackCondition $emvFallbackCondition
     */
    public ?EmvFallbackCondition $emvFallbackCondition = null;

    /**
     * @var EmvLastChipRead $emvChipCondition
     */
    public ?EmvLastChipRead $emvChipCondition = null;

    /**
     * @var float $surchargeAmount
     */
    public ?float $surchargeAmount = null;

    /** @var PaymentMethodUsageMode $paymentMethodUsageMode */
    public mixed $paymentMethodUsageMode = null;

    /** @var PhoneNumber */
    public ?PhoneNumber $homePhone = null;

    /** @var PhoneNumber */
    public ?PhoneNumber $workPhone = null;

    /** @var PhoneNumber */
    public ?PhoneNumber $shippingPhone = null;

    /** @var RemittanceReferenceType */
    public mixed $remittanceReferenceType = null;

    /** @var string */
    public ?string $remittanceReferenceValue = null;

    /** @var BNPLShippingMethod */
    public ?BNPLShippingMethod $bnplShippingMethod = null;

    /** @var boolean */
    public ?bool $maskedDataResponse = null;

    public BlockedCardType $cardTypesBlocking;

    /** @var MerchantCategory */
    public mixed $merchantCategory = null;

    /** @var string|CreditDebitIndicator */
    public string $creditDebitIndicator;

    /** @var ?array */
    public ?array $bills = null;

    /** @var string */
    public string $clerkId;

    /** @var string */
    public ?string $shippingDate = null;

    /**
     * 
     * @param TransactionType $type Request transaction type
     * @param ?IPaymentMethod $paymentMethod Request payment method
     */
    public function __construct(TransactionType|string $type, ?IPaymentMethod $paymentMethod = null)
    {
        parent::__construct($type, $paymentMethod);
        $this->withPaymentMethod($paymentMethod);
        $this->supplementaryData = [];
    }

    /**
     * {@inheritdoc}
     *
     * @return Transaction
     */
    public function execute(string $configName = 'default'): Transaction
    {
        parent::execute($configName);

        $client = ServicesContainer::instance()->getClient($configName);
        if ($client->supportsOpenBanking() && $this->paymentMethod instanceof BankPayment) {
            $obClient = ServicesContainer::instance()->getOpenBanking($configName);
            if (get_class($obClient) != get_class($client)) {
                return $obClient->processOpenBanking($this);
            }
        }

        return $client->processAuthorization($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return String
     */
    public function serialize(string $configName = 'default'): string
    {
        $this->transactionModifier = TransactionModifier::HOSTEDREQUEST;
        parent::execute();

        $client = ServicesContainer::instance()->getClient($configName);

        if ($client->supportsHostedPayments()) {
            return $client->serializeRequest($this);
        }

        throw new UnsupportedTransactionException("Your current gateway does not support hosted payments.");
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function setupValidations(): void
    {
        $this->validations->of(
            TransactionType::AUTH |
                TransactionType::SALE |
                TransactionType::REFUND |
                TransactionType::ADD_VALUE
        )
            ->with(TransactionModifier::NONE)
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull()
            ->check('paymentMethod')->isNotNull();

        $this->validations->of(
            TransactionType::AUTH |
                TransactionType::SALE
        )
            ->with(TransactionModifier::HOSTEDREQUEST)
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull();

        $this->validations->of(
            TransactionType::AUTH |
                TransactionType::SALE
        )
            ->with(TransactionModifier::OFFLINE)
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull()
            ->check('offlineAuthCode')->isNotNull();

        $this->validations->of(TransactionType::BALANCE)
            ->check('paymentMethod')->isNotNull();

        $this->validations->of(TransactionType::ALIAS)
            ->check('aliasAction')->isNotNull()
            ->check('alias')->isNotNull();

        $this->validations->of(TransactionType::REPLACE)
            ->check('replacementCard')->isNotNull();

        $this->validations->of(
            TransactionType::AUTH |
                TransactionType::SALE
        )
            ->with(TransactionModifier::ENCRYPTED_MOBILE)
            ->check('paymentMethod')->isNotNull()
            ->check('token')->isNotNullInSubProperty('paymentMethod')
            ->check('mobileType')->isNotNullInSubProperty('paymentMethod');

        $this->validations->of(
            TransactionType::VERIFY
        )
            ->with(TransactionModifier::HOSTEDREQUEST)
            ->check('currency')->isNotNull();

        $this->validations->of(
            TransactionType::AUTH |
                TransactionType::SALE
        )
            ->with(TransactionModifier::ALTERNATIVE_PAYMENT_METHOD)
            ->check('amount')->isNotNull()
            ->check('currency')->isNotNull()
            ->check('paymentMethod')->isNotNull()
            ->check('alternativePaymentMethodType')->isNotNullInSubProperty('paymentMethod')
            ->check('returnUrl')->isNotNullInSubProperty('paymentMethod')
            ->check('statusUpdateUrl')->isNotNullInSubProperty('paymentMethod')
            ->check('country')->isNotNullInSubProperty('paymentMethod')
            ->check('accountHolderName')->isNotNullInSubProperty('paymentMethod');
    }

    /**
     * Set an address value; where applicable.
     *
     * Currently supports billing and shipping addresses.
     *
     * @param Address $address The desired address information
     * @param AddressType|string $type The desired address type
     *
     * @return AuthorizationBuilder
     */
    public function withAddress(Address $address, AddressType|string $type = AddressType::BILLING): self
    {
        $address->type = $type;
        if ($type === AddressType::BILLING) {
            $this->billingAddress = $address;
        } else {
            $this->shippingAddress = $address;
        }
        return $this;
    }

    /**
     * Set the request alias
     *
     * @param string $aliasAction Request alias action
     * @param string $alias Request alias
     *
     * @return AuthorizationBuilder
     * @internal
     */
    public function withAlias(AliasAction|string $aliasAction, string $alias): self
    {
        $this->aliasAction = $aliasAction;
        $this->alias = $alias;
        return $this;
    }

    /**
     * Set the request to allow duplicates
     *
     * @param bool $allowDuplicates Request to allow duplicates
     *
     * @return AuthorizationBuilder
     */
    public function withAllowDuplicates($allowDuplicates): self
    {
        $this->allowDuplicates = $allowDuplicates;
        return $this;
    }

    /**
     * Set the request to allow a partial authorization
     *
     * @param bool $allowPartialAuth Request to allow a partial authorization
     *
     * @return AuthorizationBuilder
     */
    public function withAllowPartialAuth(bool $allowPartialAuth): self
    {
        $this->allowPartialAuth = $allowPartialAuth;
        return $this;
    }

    /**
     * Set the request amount
     *
     * @param string|float $amount Request amount
     *
     * @return AuthorizationBuilder
     */
    public function withAmount(string|float|null $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /** @return AuthorizationBuilder */
    public function withAmountEstimated(bool $value): self
    {
        $this->amountEstimated = $value;
        return $this;
    }

    /**
     * Set the request authorization amount
     *
     * @param string|float $authAmount Request authorization amount
     *
     * @return AuthorizationBuilder
     */
    public function withAuthAmount(string|float $authAmount): self
    {
        $this->authAmount = $authAmount;
        return $this;
    }

    /**
     * Sets the auto substantiation values for the transaction
     *
     * @param AutoSubstantiation
     *
     * @return AuthorizationBuilder
     */
    public function withAutoSubstantiation(AutoSubstantiation $autoSubstantiation): self
    {
        $this->autoSubstantiation = $autoSubstantiation;
        return $this;
    }

    /**
     * Sets the commercial data values for use w/ lvl2 & lvl3 transactions
     *
     * @param CommercialData
     *
     * @return AuthorizationBuilder
     */
    public function withCommercialData(mixed $commercialData): self
    {
        $this->commercialData = $commercialData;
        return $this;
    }

    /**
     * Set the request's balance inquiry type
     *
     * @param string $balanceInquiryType Balance inquiry type
     *
     * @return AuthorizationBuilder
     */
    public function withBalanceInquiryType(InquiryType|string $balanceInquiryType): self
    {
        $this->balanceInquiryType = $balanceInquiryType;
        return $this;
    }

    /**
     * Set Card On File Indicator
     *
     * @param bool $cardOnFile
     *
     * @return AuthorizationBuilder
     */
    public function withCardOnFile(bool $cardOnFile): self
    {
        $this->cardOnFile = $cardOnFile;
        return $this;
    }

    /**
     * Set the request cashback amount
     *
     * @param string|float $cashbackAmount Request cashback amount
     *
     * @return AuthorizationBuilder
     */
    public function withCashBack(string|float $cashBackAmount): self
    {
        $this->cashBackAmount = $cashBackAmount;
        $this->transactionModifier = TransactionModifier::CASH_BACK;
        return $this;
    }

    /**
     * Set the Client Transaction Id
     *
     * @param string $clientTransactionId
     *
     * @return AuthorizationBuilder
     */
    public function withClientTransactionId(string $clientTransactionId): self
    {
        if ($this->transactionType === TransactionType::REVERSAL) {
            if ($this->paymentMethod instanceof TransactionReference) {
                $this->paymentMethod->clientTransactionId = $clientTransactionId;
            } else {
                /** @var TransactionReference */
                $ref = new TransactionReference();
                $ref->clientTransactionId = $clientTransactionId;
                if ($this->paymentMethod !== null) {
                    $ref->paymentMethodType = $this->paymentMethod;
                }

                $this->paymentMethod = $ref;
            }
        } else {
            $this->clientTransactionId = $clientTransactionId;
        }

        return $this;
    }

    /**
     * Set the request currency
     *
     * @param string $currency Request currency
     *
     * @return AuthorizationBuilder
     */
    public function withCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set the request customer ID
     *
     * @param string|float $customerId Request customer ID
     *
     * @return AuthorizationBuilder
     */
    public function withCustomerId(string|float $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * Set the request customer IP address
     *
     * @param string|float $customerIpAddress Request customer IP address
     *
     * @return AuthorizationBuilder
     */
    public function withCustomerIpAddress(string|float $customerIpAddress): self
    {
        $this->customerIpAddress = $customerIpAddress;
        return $this;
    }

    /**
     * Set the request customer Data
     *
     * @param Customer $customerData Request customer Data
     *
     * @return AuthorizationBuilder
     */
    public function withCustomerData(Customer $customerData): self
    {
        $this->customerData = $customerData;
        if (!empty($customerData->id)) {
            $this->customerId = $customerData->id;
        }

        return $this;
    }

    /**
     * Set the request customData
     *
     * @param string $customData Request customData
     *
     * @return AuthorizationBuilder
     */
    public function withCustomData(array|string $customData): self
    {
        $this->customData = $customData;
        return $this;
    }

    /**
     * Set the request description
     *
     * @param string $description Request description
     *
     * @return AuthorizationBuilder
     */
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the request decisionManager
     *
     * @param DecisionManager $decisionManager Request decisionManager
     *
     * @return AuthorizationBuilder
     */
    public function withDecisionManager(DecisionManager $decisionManager): self
    {
        $this->decisionManager = $decisionManager;
        return $this;
    }

    /**
     * Set the request dynamic descriptor
     *
     * @param string $dynamicDescriptor Request dynamic descriptor
     *
     * @return AuthorizationBuilder
     */
    public function withDynamicDescriptor(string $dynamicDescriptor): self
    {
        $this->dynamicDescriptor = $dynamicDescriptor;
        return $this;
    }

    /**
     * Set the request gratuity
     *
     * @param string|amount $gratuity Request gratuity
     *
     * @return AuthorizationBuilder
     */
    public function withGratuity(string|float $gratuity): self
    {
        $this->gratuity = $gratuity;
        return $this;
    }

    /**
     * Set the request invoice number
     *
     * @param string|float $invoiceNumber Request invoice number
     *
     * @return AuthorizationBuilder
     */
    public function withInvoiceNumber(string|float $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    /**
     * Set the request to request Level II or III
     *
     * @param bool $level2Request Request to request Level II or III
     *
     * @return AuthorizationBuilder
     */
    public function withCommercialRequest(bool $level2or3Request): self
    {
        $this->level2Request = $level2or3Request;
        return $this;
    }

    /**
     * Set the request offline authorization code
     *
     * @param string $offlineAuthCode Authorization code from offline authorization
     *
     * @return AuthorizationBuilder
     */
    public function withOfflineAuthCode(string $offlineAuthCode): self
    {
        $this->offlineAuthCode = $offlineAuthCode;
        $this->transactionModifier = TransactionModifier::OFFLINE;
        return $this;
    }

    /**
     * Sets the one-time payment flag; where applicable.
     *
     * This is only useful when using recurring payment profiles for
     * one-time payments that are not a part of a recurring schedule.
     *
     * @param boolean $value The one-time flag
     *
     * @return AuthorizationBuilder
     */
    public function withOneTimePayment(bool $value): self
    {
        $this->oneTimePayment = $value;
        $this->transactionModifier = TransactionModifier::RECURRING;
        return $this;
    }

    /**
     * Set the request order ID
     *
     * @param string|float $orderId Request order ID
     *
     * @return AuthorizationBuilder
     */
    public function withOrderId(string|float|null $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Set the request payment method
     *
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return AuthorizationBuilder
     */
    public function withPaymentMethod(?IPaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        if ($paymentMethod instanceof EBTCardData && $paymentMethod->serialNumber !== null) {
            $this->transactionModifier = TransactionModifier::VOUCHER;
        }
        return $this;
    }

    /**
     * Set the request productData
     *
     * @param array $productData Request productData
     *
     * @return AuthorizationBuilder
     */
    public function withProductData(array $productData): self
    {
        $this->productData = $productData;
        return $this;
    }

    /**
     * Set the request product ID
     *
     * @param string|float $productId Request product ID
     *
     * @return AuthorizationBuilder
     */
    public function withProductId(string|float $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * Set the request to request multi-use token
     *
     * @param bool $requestMultiUseToken Request multi-use token in gateway response
     * @param bool $requestUniqueToken Portico-specific parameter to make gateway create
     * a unique MUT regardless of PAN that is tokenized
     *
     * @return AuthorizationBuilder
     */
    public function withRequestMultiUseToken(bool $requestMultiUseToken, bool $requestUniqueToken = false): AuthorizationBuilder
    {
        $this->requestMultiUseToken = $requestMultiUseToken;
        $this->requestUniqueToken = $requestUniqueToken;
        return $this;
    }

    /**
     * Previous request's transaction ID
     *
     * @param string $transactionId Transaction ID
     *
     * @return AuthorizationBuilder
     */
    public function withTransactionId(string $transactionId): self
    {
        $this->paymentMethod = new TransactionReference($transactionId);
        return $this;
    }

    /**
     * Set the request's ecommerce info
     *
     * @param EcommerceInfo $ecommerceInfo Ecommerce info
     *
     * @return AuthorizationBuilder
     */
    public function withEcommerceInfo(EcommerceInfo $ecommerceInfo): self
    {
        $this->ecommerceInfo = $ecommerceInfo;
        return $this;
    }

    /**
     * Set the request's replacement gift card
     *
     * @param GiftCard $replacementCard replacement gift card
     *
     * @return AuthorizationBuilder
     */
    public function withReplacementCard(GiftCard $replacementCard): self
    {
        $this->replacementCard = $replacementCard;
        return $this;
    }

    /**
     * Set the request CVN
     *
     * @param string|float $cvn Request cvn
     *
     * @return AuthorizationBuilder
     */
    public function withCvn(string|float $cvn): self
    {
        $this->cvn = $cvn;
        return $this;
    }

    /**
     * Set the request recurringType and recurringSequence
     *
     * @param RecurringType $recurringType & RecurringSequence $recurringSequence
     *
     * @return AuthorizationBuilder
     */
    public function withRecurringInfo(RecurringType|string $recurringType, RecurringSequence|string $recurringSequence): self
    {
        $this->recurringType = $recurringType;
        $this->recurringSequence = $recurringSequence;
        return $this;
    }

    /**
     * Set the request dccRateData
     *
     * @param DccRateData dccRateData
     *
     * @return AuthorizationBuilder
     */
    public function withDccRateData(DccRateData $value): self
    {
        $this->dccRateData = $value;
        return $this;
    }

    /**
     * Set the request Convenience amount
     *
     * @param string|float $convenienceAmount Request Convenience amount
     *
     * @return AuthorizationBuilder
     */
    public function withConvenienceAmount(string|float $convenienceAmount): self
    {
        if (!empty($this->convenienceAmount)) {
            $this->convenienceAmount = 0.0;
        }

        $this->convenienceAmount = $convenienceAmount;
        return $this;
    }

    /**
     * Set the request shippingAmount
     *
     * @param string|float $shippingAmount Request shippingAmount
     *
     * @return AuthorizationBuilder
     */
    public function withShippingAmount(string|float $shippingAmount): self
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * Set the request shippingDiscount
     *
     * @param string|float $shippingDiscount Request shippingDiscount
     *
     * @return AuthorizationBuilder
     */
    public function withShippingDiscount(string|float $shippingDiscount): self
    {
        $this->shippingDiscount = $shippingDiscount;
        return $this;
    }

    /**
     * @param OrderDetails $orderDetails
     * @return AuthorizationBuilder
     */
    public function withOrderDetails(OrderDetails $orderDetails): self
    {
        $this->orderDetails = $orderDetails;
        return $this;
    }

    /**
     * @param StoredCredential $storedCredential
     * @return AuthorizationBuilder
     */
    public function withStoredCredential(StoredCredential $storedCredential): self
    {
        $this->storedCredential = $storedCredential;
        return $this;
    }

    public function withInstallment(InstallmentData $installment): AuthorizationBuilder
    {
        $this->installment = $installment;
        return $this;
    }

    /**
     * @param FraudFilterMode $fraudFilter
     * @param FraudRuleCollection $fraudRules
     *
     * @return $this
     */
    public function withFraudFilter(FraudFilterMode|string $fraudFilter, ?FraudRuleCollection $fraudRules = null): self
    {
        $this->fraudFilter = $fraudFilter;
        if (!empty($fraudRules)) {
            $this->fraudRules = $fraudRules->rules;
        }
        return $this;
    }

    /**
     * Set whether AVS requested
     *
     * @param string|bool $verifyAddress
     *
     * @return AuthorizationBuilder
     */
    public function withVerifyAddress(string|bool $verifyAddress): self
    {
        $this->verifyAddress = $verifyAddress;
        return $this;
    }

    /**
     * Set the timestamp
     *
     * @param string $timestamp
     *
     * @return AuthorizationBuilder
     */
    public function withTimeStamp(string|float $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * Set the hostedPaymentData
     *
     * @param string $hostedPaymentData
     *
     * @return AuthorizationBuilder
     */
    public function withHostedPaymentData(HostedPaymentData|HPPData $hostedPaymentData): self
    {
        $this->hostedPaymentData = $hostedPaymentData;
        return $this;
    }

    /**
     * Set the associated schedule ID
     *
     * @param string $scheduleId
     *
     * @return AuthorizationBuilder
     */
    public function withScheduleId(string $scheduleId): self
    {
        $this->scheduleId = $scheduleId;
        return $this;
    }

    /**
     * Set the Discount Details
     *
     * @param string $discountDetails
     *
     * @return AuthorizationBuilder
     */
    public function withDiscountDetails(string $discountDetails): self
    {
        $this->discountDetails = $discountDetails;
        return $this;
    }

    /**
     * Set the cash tendered amount
     *
     * @param string $cashTendered
     *
     * @return AuthorizationBuilder
     */
    public function withCashTenderedDetails(string $cashTendered): self
    {
        $this->cashTendered = $cashTendered;
        return $this;
    }

    /**
     * Used to send 'Card On File Data'
     *
     * @param string $transactionInitiator
     * @param string $cardBrandTransactionId
     * @param string $categoryIndicator https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#PosGateway_xsd~c-CardOnFileDataType~e-CategoryInd.html
     *
     * @return AuthorizationBuilder
     */
    public function withCardBrandStorage(
        string $transactionInitiator,
        string $cardBrandTransactionId = '',
        string $categoryIndicator = ''
    ) : AuthorizationBuilder
    {
        $this->transactionInitiator = $transactionInitiator;
        $this->cardBrandTransactionId = $cardBrandTransactionId;
        $this->categoryIndicator = $categoryIndicator;
        return $this;
    }

    /**
     * Set lastRegisteredDate - DD/MM/YYYY
     * Used w/TransIT gateway
     *
     * @param string $date
     *
     * @return AuthorizationBuilder
     */
    public function withLastRegisteredDate(mixed $date): self
    {
        $this->lastRegisteredDate = $date;
        return $this;
    }

    /**
     * Set the Multi Capture.
     *
     * @param boolean $multiCapture
     *
     * @return $this
     */
    public function withMultiCapture(bool $multiCapture = false, int $paymentCount = 1) : AuthorizationBuilder
    {
        $this->multiCapture = $multiCapture;
        if ($multiCapture === true) {
            $this->multiCapturePaymentCount = $paymentCount;
        }
        return $this;
    }

    /**
     * Set shippingDate - YYYY/MM/DD
     * Used w/TransactionApi gateway
     *
     * @param string $date
     *
     * @return AuthorizationBuilder
     */
    public function withShippingDate(string $date): self
    {
        $this->shippingDate = $date;
        return $this;
    }

    /**
     * Set the Tag Data
     *
     * @param string $value
     *
     * @return $this
     */
    public function withTagData(string $value): self
    {
        $this->tagData = $value;

        return $this;
    }

    /**
     * Sets the Idempotency Key.
     *
     * @param string $value
     *
     * @return $this
     */
    public function withIdempotencyKey(string $value): self
    {
        $this->idempotencyKey = $value;

        return $this;
    }

    public function hasEmvFallbackData(): bool
    {
        return (!is_null($this->emvFallbackCondition) || !is_null($this->emvLastChipRead) || !empty($this->paymentApplicationVersion));
    }

    /**
     * @param EmvFallbackCondition $condition
     * @param EmvLastChipRead $lastRead
     * @param string $appVersion
     */
    public function withEmvFallbackData(EmvFallbackCondition|string $condition, EmvLastChipRead|string $lastRead, ?string $appVersion = null): self
    {
        $this->emvFallbackCondition = $condition;
        $this->emvLastChipRead = $lastRead;
        $this->paymentApplicationVersion = $appVersion;
        return $this;
    }

    /**
     * @param EmvLastChipRead $value
     */
    public function withChipCondition(EmvLastChipRead|string $value): self
    {
        $this->emvChipCondition = $value;

        return $this;
    }

    /**
     * Set the request clerkId
     *
     * @param string|integer $clerkId Request clerkId
     *
     * @return AuthorizationBuilder
     */
    public function withClerkId(string|int $clerkId): self
    {
        $this->clerkId = $clerkId;
        return $this;
    }

    /**
     * @param float $value
     * @param string|CreditDebitIndicator $creditDebitIndicator
     *
     * @return AuthorizationBuilder
     */
    public function withSurchargeAmount(float $value, string|CreditDebitIndicator|null $creditDebitIndicator = null): self
    {
        $this->surchargeAmount = $value;
        $this->creditDebitIndicator = $creditDebitIndicator;

        return $this;
    }

    /**
     * Set the request to use usage_mode
     *
     * @param string $value
     *
     * @return AuthorizationBuilder
     */
    public function withPaymentMethodUsageMode(PaymentMethodUsageMode|string $value): self
    {
        $this->paymentMethodUsageMode = $value;

        return $this;
    }

    /**
     * @param string $phoneCountryCode
     * @param string $number
     * @param string|PhoneNumberType $type
     *
     * @return AuthorizationBuilder
     */
    public function withPhoneNumber(string $phoneCountryCode, string $number, string|PhoneNumberType $type): self
    {
        $phoneNumber = new PhoneNumber($phoneCountryCode, $number, $type);
        switch ($phoneNumber->type) {
            case PhoneNumberType::HOME:
                $this->homePhone = $phoneNumber;
                break;
            case PhoneNumberType::WORK:
                $this->workPhone = $phoneNumber;
                break;
            case PhoneNumberType::SHIPPING:
                $this->shippingPhone = $phoneNumber;
                break;
            default:
                break;
        }
        return $this;
    }

    /**
     * Set Remittance Reference
     *
     * @param string $remittanceReferenceType
     * @param string $remittanceReferenceValue
     *
     * @return AuthorizationBuilder
     */
    public function withRemittanceReference(RemittanceReferenceType|string $remittanceReferenceType, string $remittanceReferenceValue): self
    {
        $this->remittanceReferenceType = $remittanceReferenceType;
        $this->remittanceReferenceValue = $remittanceReferenceValue;

        return $this;
    }

    /**
     * @param BNPLShippingMethod $bnpShippingMethod
     *
     * @return $this
     * @throws ArgumentException
     */
    public function withBNPLShippingMethod(BNPLShippingMethod|string $bnpShippingMethod): self
    {
        if (!$this->paymentMethod instanceof BNPL) {
            throw new ArgumentException("The selected payment method doesn't support this property!");
        }
        $this->bnplShippingMethod = $bnpShippingMethod;
        return $this;
    }

    /**
     * Indicates whether some date will be masked in the response.
     * Ex: Personally Identifiable Information (PII) etc.
     *
     * @param boolean $value
     * @return $this
     */
    public function withMaskedDataResponse(bool $value): self
    {
        $this->maskedDataResponse = $value;

        return $this;
    }

    public function withBlockedCardType(BlockedCardType $cardTypesBlocking) : AuthorizationBuilder
    {
        $vars = get_object_vars($cardTypesBlocking);
        if (empty(array_filter($vars))) {
            $array = explode('\\', get_class($cardTypesBlocking));
            throw new BuilderException(sprintf('No properties set on the %s object', end($array)));
        }
        $this->cardTypesBlocking = $cardTypesBlocking;

        return $this;
    }

    /**
     * @param MerchantCategory $merchantCategory
     */
    public function withMerchantCategory(MerchantCategory|string $merchantCategory): self
    {
        $this->merchantCategory = $merchantCategory;
        return $this;
    }

    /**
     * @param Bill
     */
    public function withBill(Bill $bill): self
    {
        if ($this->bills === null) {
            $this->bills = array();
        }

        array_push($this->bills, $bill);
        return $this;
    }

    /**
     * @param array<Bill>
     */
    public function withBills(array $bills): self
    {
        if ($this->bills === null) {
            $this->bills = array();
        }

        foreach($bills as $bill) {
            array_push($this->bills, $bill);
        }

        return $this;
    }
}
