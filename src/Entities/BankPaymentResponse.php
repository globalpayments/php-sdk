<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\BankPaymentType;

/**
 * Bank transfer/ open banking response data
 */
class BankPaymentResponse
{
    /**
     * The open banking transaction id
     * @var string
     */
    public $id;

    /**
     * URL to redirect the customer to
     * Sent there so merchant can redirect consumer to complete the payment.
     *
     * @var string
     */
    public $redirectUrl;

    /** @var string */
    public $paymentStatus;

    /** @var BankPaymentType */
    public $type;

    /** @var string */
    public $tokenRequestId;

    /** @var string */
    public $sortCode;

    /** @var string */
    public $accountName;

    /** @var string */
    public $accountNumber;

    /** @var string */
    public $iban;

    /** @var string */
    public $remittanceReferenceValue;

    /** @var string */
    public $remittanceReferenceType;
}