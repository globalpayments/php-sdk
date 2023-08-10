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
     */
    public string $id;
    /**
     * URL to redirect the customer to
     * Sent there so merchant can redirect consumer to complete the payment.
     */
    public ?string $redirectUrl;
    public ?string $paymentStatus;
    /** @var BankPaymentType */
    public $type;
    public ?string $tokenRequestId;
    public ?string $sortCode;
    public ?string $accountName;
    public ?string $accountNumber;
    public ?string $iban;
    public ?string $remittanceReferenceValue;
    public ?string $remittanceReferenceType;
    public ?float $amount;
    public ?string $currency;
    public ?string $maskedIbanLast4;
}