<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\BNPLResponse;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class TransactionReference implements IPaymentMethod
{
    /**
     * Previous transaction's payment method type
     *
     * @var PaymentMethodType
     */
    public $paymentMethodType;

    /**
     * Previous transaction's authorization code
     *
     * Useful for when referencing offline authorizations.
     *
     * @var string
     */
    public $authCode;

    /**
     * Previous authorization's transaction ID
     *
     * @var string
     */
    public $transactionId;

    /**
     * Previous authorization's transaction ID
     *
     * @var string
     */
    public $clientTransactionId;

    /**
     * Previous authorization's creditsale ID
     *
     * @var string
     */
    public $creditsaleId;

    public $orderId;

    /** @var AlternativePaymentResponse $alternativePaymentResponse */
    public $alternativePaymentResponse;

    /** @var BNPLResponse $bnplResponse */
    public $bnplResponse;
}
