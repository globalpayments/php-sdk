<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\BNPLResponse;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\TransferFundsAccountCollection;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class TransactionReference implements IPaymentMethod
{
    /**
     * Previous transaction's payment method type
     *
     * @var PaymentMethodType
     */
    public mixed $paymentMethodType = PaymentMethodType::REFERENCE;

    /**
     * Previous transaction's authorization code
     *
     * Useful for when referencing offline authorizations.
     *
     * @var string
     */
    public ?string $authCode = null;

    /**
     * Previous authorization's transaction ID
     *
     * @var string
     */
    public ?string $transactionId = null;

    /**
     * Previous authorization's transaction ID
     *
     * @var string
     */
    public ?string $clientTransactionId = null;

    /**
     * Previous authorization's creditsale ID
     *
     * @var string
     */
    public ?string $creditsaleId = null;

    public ?string $orderId = null;

    /** @var AlternativePaymentResponse $alternativePaymentResponse */
    public ?AlternativePaymentResponse $alternativePaymentResponse = null;

    /** @var BNPLResponse $bnplResponse */
    public ?BNPLResponse $bnplResponse = null;

    /** @var TransferFundsAccountCollection */
    public ?TransferFundsAccountCollection $transfersFundsAccount = null;

    /** @return PaymentMethodType */
    function getPaymentMethodType()
    {
        return $this->paymentMethodType;
    }
}
