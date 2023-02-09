<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\{AuthorizationBuilder, ManagementBuilder};
use GlobalPayments\Api\Entities\{DccRateData, ThreeDSecure};
use GlobalPayments\Api\Entities\Enums\{
    EntryMethod,
    ManualEntryMethod,
    PaymentMethodType,
    PaymentMethodUsageMode,
    TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\PaymentMethods\Interfaces\{
    IAuthable,
    IPaymentMethod,
    IEncryptable,
    ITokenizable,
    IChargable,
    IRefundable,
    IReversable,
    IVerifyable,
    IPrePayable,
    IBalanceable,
    ISecure3d
};

abstract class Credit implements
    IPaymentMethod,
    IEncryptable,
    ITokenizable,
    IChargable,
    IAuthable,
    IRefundable,
    IReversable,
    IVerifyable,
    IPrePayable,
    IBalanceable,
    ISecure3d
{
    /**
     * The card type of the manual entry data.
     */
    public $cardType;

    /**
     * The authentication value use to verify the validity of the digit wallet transaction.
     *
     * @var string
     */
    public $cryptogram;

    /**
     * Electronic commerce indicator
     *
     * @var string
     */
    public $eci;

    public $encryptionData;

    /** @var EntryMethod|ManualEntryMethod */
    public $entryMethod;

    /** @var bool */
    public $isFleet;

    /**
     * The type of mobile device used in `TransactionModifier.Encrypted_Mobile`
     * transactions.
     */
    public $mobileType;

    public $paymentMethodType = PaymentMethodType::CREDIT;


    // maybe change the name of the below var


    /** @var PaymentDataSourceType */
    public $paymentSource;

    /**
     * Secure 3d Data attached to the card
     * @var ThreeDSecure
     */
    public $threeDSecure;

    /**
     * The token value representing the card.
     *
     * For `TransactionModifier.Encrypted_Mobile` transactions, this value is the
     * encrypted payload from the mobile payment scheme.
     */
    public $token;

    /**
     * Authorizes the payment method
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function authorize($amount = null, $isEstimated = false)
    {
        return (new AuthorizationBuilder(TransactionType::AUTH, $this))
            ->withAmount($amount != null ? $amount : ($this->threeDSecure != null ? $this->threeDSecure->getAmount() : null))
            ->withCurrency($this->threeDSecure != null ? $this->threeDSecure->getCurrency() : null)
            ->withOrderId($this->threeDSecure != null ? $this->threeDSecure->getOrderId() : null)
            ->withAmountEstimated($isEstimated);
    }

    /**
     * Authorizes the payment method and captures the entire authorized amount
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function charge($amount = null)
    {
      
        return (new AuthorizationBuilder(TransactionType::SALE, $this))
            ->withAmount($amount != null ? $amount : ($this->threeDSecure != null ? $this->threeDSecure->getAmount() : null))
            ->withCurrency($this->threeDSecure != null ? $this->threeDSecure->getCurrency() : null)
            ->withOrderId($this->threeDSecure != null ? $this->threeDSecure->getOrderId() : null);
    }

    /**
     * Adds value to the payment method
     *
     * @param string|float $amount Amount to add
     *
     * @return AuthorizationBuilder
     */
    public function addValue($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::ADD_VALUE, $this))
            ->withAmount($amount);
    }

    /**
     * Inquires the balance of the payment method
     *
     * @param InquiryType $inquiry Type of inquiry
     *
     * @return AuthorizationBuilder
     */
    public function balanceInquiry($inquiry = null)
    {
        return (new AuthorizationBuilder(TransactionType::BALANCE, $this))
            ->withBalanceInquiryType($inquiry);
    }

    /**
     * Refunds the payment method
     *
     * @param string|float $amount Amount to refund
     *
     * @return AuthorizationBuilder
     */
    public function refund($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REFUND, $this))
            ->withAmount($amount);
    }

    /**
     * Reverses the payment method
     *
     * @param string|float $amount Amount to reverse
     *
     * @return AuthorizationBuilder
     */
    public function reverse($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REVERSAL, $this))
            ->withAmount($amount);
    }

    /**
     * Verifies the payment method
     *
     * @return AuthorizationBuilder
     */
    public function verify()
    {
        return new AuthorizationBuilder(TransactionType::VERIFY, $this);
    }

    /**
     * Tokenizes the payment method
     *
     * @param bool $verifyCard
     * @param string $usageMode
     *
     * @return AuthorizationBuilder
     */
    public function tokenize($verifyCard = true, $usageMode = PaymentMethodUsageMode::MULTIPLE)
    {
        if ($verifyCard !== false) {
            $verifyCard = true;
        }
        $type = $verifyCard ? TransactionType::VERIFY : TransactionType::TOKENIZE;

        return (new AuthorizationBuilder($type, $this))
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode($usageMode);
    }

    /**
     * Updates the token expiry date with the values proced to the card object
     *
     * @return bool value indicating success/failure
     */
    public function updateTokenExpiry()
    {
        if (empty($this->token)) {
            throw new BuilderException('Token cannot be null');
        }

        (new ManagementBuilder(TransactionType::TOKEN_UPDATE))
            ->withPaymentMethod($this)
            ->execute();

        return true;
    }

    /**
     * Updates the payment token
     *
     * @return ManagementBuilder
     */
    public function updateToken()
    {
        if (empty($this->token)) {
            throw new BuilderException('Token cannot be null');
        }

        return (new ManagementBuilder(TransactionType::TOKEN_UPDATE))
            ->withPaymentMethod($this);
    }
    
    /**
     * Deletes the token associated with the current card object
     *
     * @return bool value indicating success/failure
     */
    public function deleteToken()
    {
        if (empty($this->token)) {
            throw new BuilderException('Token cannot be null');
        }

        (new ManagementBuilder(TransactionType::TOKEN_DELETE))
            ->withPaymentMethod($this)
            ->execute();

        return true;
    }

    public function getDccRate($dccRateType = null, $ccp = null)
    {
        if (!empty($dccRateType) || !empty($ccp)) {
            $dccRateData = new DccRateData();
            $dccRateData->dccProcessor = $ccp;
            $dccRateData->dccRateType = $dccRateType;
        }
        $authBuilder = new AuthorizationBuilder(TransactionType::DCC_RATE_LOOKUP, $this);
        if (!empty($dccRateData)) {
            $authBuilder->withDccRateData($dccRateData);
        }

        return $authBuilder;
    }

    public function detokenize()
    {
        if (empty($this->token)) {
            throw new BuilderException("Token cannot be null or empty");
        }

        return (new ManagementBuilder(TransactionType::DETOKENIZE, $this))
            ->execute();
    }
}
