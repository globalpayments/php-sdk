<?php

namespace GlobalPayments\Api\Entities;

/**
 * Alternative payment response data
 */
class AlternativePaymentResponse
{
    /**
     * bank account details
     *
     * @var string|null
     */
    public $bankAccount;

    /**
     * Account holder name of the customer’s account
     *
     * @var string|null
     */
    public $accountHolderName;

    /**
     * 2 character ISO country code
     *
     * @var string
     */
    public $country;

    /**
     * URL to redirect the customer to - only available in PENDING asynchronous transactions.
     * Sent there so merchant can redirect consumer to complete an interrupted payment.
     *
     * @var float|string|null
     */
    public $redirectUrl;

    /**
     * This parameter reflects what the customer will see on the proof of payment
     * (for example, bank statement record and similar). Also known as the payment descriptor
     *
     * @var string
     */
    public $paymentPurpose;
    
    /**
     *
     * @var string
     */
    public $paymentMethod;

    /**
     * The provider reference
     *
     * @var string
     */
    public $providerReference;

    /**
     * The APM provider name
     *
     * @var string
     */
    public $providerName;

    /** @var string */
    public $ack;
    /** @var string */
    public $sessionToken;
    /** @var string */
    public $correlationReference;
    /** @var string */
    public $versionReference;
    /** @var string */
    public $buildReference;
    public $timeCreatedReference;
    public $transactionReference;
    public $secureAccountReference;
    public $reasonCode;
    public $pendingReason;
    public $grossAmount;
    public $paymentTimeReference;
    public $paymentType;
    public $paymentStatus;
    public $type;
    public $protectionEligibilty;
    public $authStatus;
    public $authAmount;
    public $authAck;
    public $authCorrelationReference;
    public $authVersionReference;
    public $authBuildReference;
    public $authPendingReason;
    public $authProtectionEligibilty;
    public $authProtectionEligibiltyType;
    public $authReference;
    public $feeAmount;
}
