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
     *
     * @var string
     */
    public $paymentMethod;
}
