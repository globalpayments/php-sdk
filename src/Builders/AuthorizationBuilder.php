<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\Entities\Enums\EmvFallbackCondition;
use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AliasAction;
use GlobalPayments\Api\Entities\Enums\InquiryType;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\EBTCardData;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\DecisionManager;

class AuthorizationBuilder extends TransactionBuilder
{

    /**
     * Request alias
     *
     * @internal
     * @var string
     */
    public $alias;

    /**
     * Request alias action
     *
     * @internal
     * @var AliasAction
     */
    public $aliasAction;

    /**
     * Request should allow duplicates
     *
     * @internal
     * @var bool
     */
    public $allowDuplicates;

    /**
     * Request should allow partial authorizations
     *
     * @internal
     * @var bool
     */
    public $allowPartialAuth;

    /**
     * Request amount
     *
     * @internal
     * @var string|float
     */
    public $amount;

    /** @var bool */
    public $amountEstimated;

    /**
     * Request authorization amount
     *
     * @internal
     * @var string|float
     */
    public $authAmount;

    /** @var AutoSubstantiation */
    public $autoSubstantiation;

    /**
     * Balance inquiry type
     *
     * @internal
     * @var InquiryType
     */
    public $balanceInquiryType;

    /**
     * Request billing address
     *
     * @internal
     * @var Address
     */
    public $billingAddress;

    /**
     * Indicates Card On File transaction
     *
     * @internal
     * @var bool
     */
    public $cardOnFile;

    /**
     * Request cashback amount
     *
     * @internal
     * @var string|float
     */
    public $cashBackAmount;

    /**
     * Request client transaction id
     *
     * @internal
     * @var string
     */
    public $clientTransactionId;

    /**
     * Request commercial data
     *
     * @internal
     * @var CommercialData
     */
    public $commercialData;

    /**
     * Request currency
     *
     * @internal
     * @var string
     */
    public $currency;

    /**
     * Request customer ID
     *
     * @internal
     * @var string|float
     */
    public $customerId;

    /**
     * Request customer IP address
     *
     * @internal
     * @var string|float
     */
    public $customerIpAddress;

    /**
     * Request customer Data
     *
     * @internal
     * @var Customer
     */
    public $customerData;

    /**
     * Request customData
     *
     * @internal
     * @var array<string>
     */
    public $customData;

    /**
     * Payment method CVN
     *
     * Only applicable for recurring payments
     *
     * @internal
     * @var string
     */
    public $cvn;

    /**
     * Request description
     *
     * @internal
     * @var string
     */
    public $description;

    /**
     * Request decisionManager
     *
     * @internal
     * @var DecisionManager
     */
    public $decisionManager;

    /**
     * Request dynamic descriptor
     *
     * @internal
     * @var string
     */
    public $dynamicDescriptor;

    /**
     * Request ecommerceInfo
     *
     * @internal
     * @var EcommerceInfo
     */
    public $ecommerceInfo;

    /**
     * Request gratuity
     *
     * @internal
     * @var string|amount
     */
    public $gratuity;

    /**
     * Request convenience amount
     *
     * @internal
     * @var string|amount
     */
    public $convenienceAmount;

    /**
     * Request shipping amount
     *
     * @internal
     * @var string|amount
     */
    public $shippingAmount;

    /**
     * @internal
     * @var StoredCredential
     */
    public $storedCredential;

    /**
     * Request hosted payment data
     *
     * @internal
     * @var HostedPaymentData
     */
    public $hostedPaymentData;

    /**
     * Request invoice number
     *
     * @internal
     * @var string|float
     */
    public $invoiceNumber;

    /**
     * Request should request Level II
     *
     * @internal
     * @var bool
     */
    public $level2Request;

    /**
     * Request offline authorization code
     *
     * @internal
     * @var string
     */
    public $offlineAuthCode;

    /**
     * Request should be considered one-time
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var bool
     */
    public $oneTimePayment;

    /**
     * Request order ID
     *
     * @internal
     * @var string|float
     */
    public $orderId;

    /**
     * Request product Data
     *
     * @internal
     * @var array<string>
     */
    public $productData;

    /**
     * Request product ID
     *
     * @internal
     * @var string|float
     */
    public $productId;

    /**
     * Request recurring sequence
     *
     * @internal
     * @var RecurringSequence
     */
    public $recurringSequence;

    /**
     * Request recurring type
     *
     * @internal
     * @var RecurringType
     */
    public $recurringType;

    /**
     * Request should request multi-use token
     *
     * @internal
     * @var bool
     */
    public $requestMultiUseToken;

    /**
     * To attach registration most recent change date value
     * For use w/Discover cards on TransIT gateway
     *
     * @internal
     * @var Date
     */
    public $lastRegisteredDate;

    /**
     * Request replacement gift card
     *
     * @internal
     * @var GiftCard
     */
    public $replacementCard;

    /**
     * Request schedule ID
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var string
     */
    public $scheduleId;

    /**
     * Request shipping address
     *
     * @internal
     * @var Address
     */
    public $shippingAddress;

    /**
     * Request timestamp
     *
     * @internal
     * @var string|float
     */
    public $timestamp;

    /**
     * Request supplementary data
     *
     * @var array<string, string>
     */
    public $supplementaryData;

    /**
     * DCC rate Data
     *
     * @internal
     * @var dccRateData
     */
    public $dccRateData;

    /**
     * DCC processor
     *
     * @internal
     * @var dccProcessor
     */
    public $dccProcessor;

    /**
     * DCC Rate Type
     *
     * @internal
     * @var dccRateType
     */
    public $dccRateType;

    /**
     * DCC Type
     *
     * @internal
     * @var dccType
     */
    public $dccType;

    /**
     * Fraud Filter
     *
     * Typically only applicable with recurring payment methods
     *
     * @internal
     * @var string
     */
    public $fraudFilter;

    /**
     * For AVS (Address verification System) request
     *
     * @internal
     * @var bool
     */
    public $verifyAddress;

    /**
     * For TransIT cash amount for a specified transaction
     * Note: If a decimal point is included, the amount reflects a dollar value.
     *       If a decimal point is not included, the amount reflects a cent value.
     *
     * @internal
     * @var string
     */
    public $cashTendered;

    /**
     * For TransIT transaction discount details
     *
     * @internal
     * @var string
     */
    public $discountDetails;

    /*
     * Card on File field
     * @var string
     *
     */
    public $cardBrandTransactionId;

    /*
     * Card on File field
     * @var string
     *
     */
    public $transactionInitiator;

    /**
     * @var string $tagData
     */
    public $tagData;

    /**
     * @var string $idempotencyKey
     */
    public $idempotencyKey;

    /**
     * @var EmvLastChipRead $emvLastChipRead
     */
    public $emvLastChipRead;

    /**
     * @var string $paymentApplicationVersion
     */
    public $paymentApplicationVersion;

    /**
     * @var EmvFallbackCondition $emvFallbackCondition
     */
    public $emvFallbackCondition;

    /**
     * @var EmvLastChipRead $emvChipCondition
     */
    public $emvChipCondition;

    /**
     * @var float $surchargeAmount
     */
    public $surchargeAmount;

    /**
     * @var PaymentMethodUsageMode $paymentMethodUsageMode
     */
    public $paymentMethodUsageMode;

    /**
     * {@inheritdoc}
     *
     * @param TransactionType $type Request transaction type
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return
     */
    public function __construct($type, IPaymentMethod $paymentMethod = null)
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
    public function execute($configName = 'default')
    {
        parent::execute($configName);
        
        $client = ServicesContainer::instance()->getClient($configName);
        return $client->processAuthorization($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return String
     */
    public function serialize($configName = 'default')
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
    protected function setupValidations()
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
    public function withAddress(Address $address, $type = AddressType::BILLING)
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
    public function withAlias($aliasAction, $alias)
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
    public function withAllowDuplicates($allowDuplicates)
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
    public function withAllowPartialAuth($allowPartialAuth)
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
    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /** @return AuthorizationBuilder */
    public function withAmountEstimated($value)
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
    public function withAuthAmount($authAmount)
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
    public function withAutoSubstantiation($autoSubstantiation)
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
    public function withCommercialData($commercialData)
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
    public function withBalanceInquiryType($balanceInquiryType)
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
    public function withCardOnFile($cardOnFile)
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
    public function withCashBack($cashBackAmount)
    {
        $this->cashBackAmount = $cashBackAmount;
        $this->transactionModifier = TransactionModifier::CASH_BACK;
        return $this;
    }

    public function withClientTransactionId($clientTransactionId)
    {
        if ($this->transactionType !== TransactionType::REVERSAL) {
            $this->clientTransactionId = $clientTransactionId;
            return $this;
        }

        if (!$this->paymentMethod instanceof TransactionReference) {
            $this->paymentMethod = new TransactionReference();
        }

        $this->paymentMethod->clientTransactionId = $clientTransactionId;
        return $this;
    }

    /**
     * Set the request currency
     *
     * @param string $currency Request currency
     *
     * @return AuthorizationBuilder
     */
    public function withCurrency($currency)
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
    public function withCustomerId($customerId)
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
    public function withCustomerIpAddress($customerIpAddress)
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
    public function withCustomerData(Customer $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }

    /**
     * Set the request customData
     *
     * @param string $customData Request customData
     *
     * @return AuthorizationBuilder
     */
    public function withCustomData($customData)
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
    public function withDescription($description)
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
    public function withDecisionManager(DecisionManager $decisionManager)
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
    public function withDynamicDescriptor($dynamicDescriptor)
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
    public function withGratuity($gratuity)
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
    public function withInvoiceNumber($invoiceNumber)
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
    public function withCommercialRequest($level2or3Request)
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
    public function withOfflineAuthCode($offlineAuthCode)
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
    public function withOneTimePayment($value)
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
    public function withOrderId($orderId)
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
    public function withPaymentMethod($paymentMethod)
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
     * @param string $productData Request productData
     *
     * @return AuthorizationBuilder
     */
    public function withProductData($productData)
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
    public function withProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * Set the request to request multi-use token
     *
     * @param bool $requestMultiUseToken Request to request multi-use token
     *
     * @return AuthorizationBuilder
     */
    public function withRequestMultiUseToken($requestMultiUseToken)
    {
        $this->requestMultiUseToken = $requestMultiUseToken;
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
    public function withEcommerceInfo(EcommerceInfo $ecommerceInfo)
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
    public function withReplacementCard(GiftCard $replacementCard)
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
    public function withCvn($cvn)
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
    public function withRecurringInfo($recurringType, $recurringSequence)
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
    public function withDccRateData($value)
    {
        $this->dccRateData = $value;
        return $this;
    }

    /**
     * Set the request dccProcessor
     *
     * @param DccProcessor dccProcessor
     *
     * @return AuthorizationBuilder
     */
    public function withDccProcessor($value)
    {
        $this->dccProcessor = $value;
        return $this;
    }

    /**
     * Set the request dccRateType
     *
     * @param DccRateType dccRateType
     *
     * @return AuthorizationBuilder
     */
    public function withDccRateType($value)
    {
        $this->dccRateType = $value;
        return $this;
    }

    /**
     * Set the request dccType
     *
     * @param string dccType
     *
     * @return AuthorizationBuilder
     */
    public function withDccType($value)
    {
        $this->dccType = $value;
        return $this;
    }

    /**
     * Set the request Convenience amount
     *
     * @param string|float $convenienceAmt Request Convenience amount
     *
     * @return AuthorizationBuilder
     */
    public function withConvenienceAmount($convenienceAmount)
    {
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
    public function withShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * @return AuthorizationBuilder
     */
    public function withStoredCredential($storedCredential)
    {
        $this->storedCredential = $storedCredential;
        return $this;
    }

    /**
     * Set the request customer IP address
     *
     * @param string|float $customerIpAddress Request customer IP address
     *
     * @return AuthorizationBuilder
     */
    public function withFraudFilter($fraudFilter)
    {
        $this->fraudFilter = $fraudFilter;
        return $this;
    }

    /**
     * Set whether AVS requested
     *
     * @param string|bool $verifyAddress
     *
     * @return AuthorizationBuilder
     */
    public function withVerifyAddress($verifyAddress)
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
    public function withTimeStamp($timestamp)
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
    public function withHostedPaymentData($hostedPaymentData)
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
    public function withScheduleId($scheduleId)
    {
        $this->scheduleId = $scheduleId;
        return $this;
    }

    /**
     * Set the associated schedule ID
     *
     * @param string $scheduleId
     *
     * @return AuthorizationBuilder
     */
    public function withDiscountDetails($discountDetails)
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
    public function withCashTenderedDetails($cashTendered)
    {
        $this->cashTendered = $cashTendered;
        return $this;
    }

    /**
     * Set the Card on File storage
     *
     * @param string $transactionInitiator
     * @param string $value
     *
     * @return AuthorizationBuilder
     */
    public function withCardBrandStorage($transactionInitiator, $value = '')
    {
        $this->transactionInitiator = $transactionInitiator;
        $this->cardBrandTransactionId = $value;
        return $this;
    }

    /**
     * Set lastRegisteredDate - DD/MM/YYYY
     * Used w/TransIT gateway
     *
     * @param bool $isRegistered
     *
     * @return AuthorizationBuilder
     */
    public function withLastRegisteredDate($date)
    {
        $this->lastRegisteredDate = $date;
        return $this;
    }

    public function withMultiCapture($multiCapture = false)
    {
        $this->multiCapture = $multiCapture;
        return $this;
    }

    /**
     * Depending on the parameters received,
     * Add supplementary data or
     * Add multiple values to the supplementaryData array
     *
     * @param string|array<string, string>  $key
     * @param string $value
     *
     * @return AuthorizationBuilder
     */
    public function withSupplementaryData($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            foreach ($key as $k => $v) {
                $this->withSupplementaryData($k, $v);
            }
        }

        if ($key && isset($value)) {
            $this->supplementaryData[$key] = (string) $value;
        }

        return $this;
    }

    /**
     * @param $value
     */
    public function withTagData($value)
    {
        $this->tagData = $value;

        return $this;
    }

    public function withIdempotencyKey($value)
    {
        $this->idempotencyKey = $value;

        return $this;
    }

    public function hasEmvFallbackData()
    {
        return (!is_null($this->emvFallbackCondition) || !is_null($this->emvLastChipRead) || !empty($this->paymentApplicationVersion));
    }

    /**
     * @param EmvFallbackCondition $condition
     * @param EmvLastChipRead $lastRead
     * @param string $appVersion
     */
    public function withEmvFallbackData($condition, $lastRead, $appVersion = null)
    {
        $this->emvFallbackCondition = $condition;
        $this->emvLastChipRead = $lastRead;
        $this->paymentApplicationVersion = $appVersion;
    }

    /**
     * @param EmvLastChipRead $value
     */
    public function withChipCondition($value)
    {
        $this->emvChipCondition = $value;

        return $this;
    }

    /**
     * @param float $value
     *
     * @return AuthorizationBuilder
     */
    public function withSurchargeAmount($value)
    {
        $this->surchargeAmount = $value;

        return $this;
    }

    /**
     * Set the request to use usage_mode
     *
     * @param string $value
     *
     * @return AuthorizationBuilder
     */
    public function withPaymentMethodUsageMode($value)
    {
        $this->paymentMethodUsageMode = $value;

        return $this;
    }
}
