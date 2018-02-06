<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\EcommerceInfo;
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

    /**
     * Request authorization amount
     *
     * @internal
     * @var string|float
     */
    public $authAmount;

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
        $this->transactionType = $type;
        $this->paymentMethod = $paymentMethod;
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
                        ->processAuthorization($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return String
     */
    public function serialize()
    {
        $transactionModifier = TransactionModifier::HOSTEDREQUEST;
        parent::execute();

        $client = ServicesContainer::instance()->getClient();

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
                ->check('amount')->isNotNull()
                ->check('currency')->isNotNull()
                ->check('paymentMethod')->isNotNull();

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
     * @internal
     * @param string $aliasAction Request alias action
     * @param string $alias Request alias
     *
     * @return AuthorizationBuilder
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
        if ($this->transactionType !== TransactionType::REVERSAL && $this->transactionType !== TransactionType::REFUND
        ) {
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
     * Set the request to request Level II
     *
     * @param bool $level2Request Request to request Level II
     *
     * @return AuthorizationBuilder
     */
    public function withCommercialRequest($level2Request)
    {
        $this->level2Request = $level2Request;
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
}
