<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\{
    BNPLType,
    PaymentMethodType,
    TransactionModifier,
    TransactionType
};
use GlobalPayments\Api\PaymentMethods\Interfaces\{IAuthable, IPaymentMethod};

class BNPL implements
    IPaymentMethod,
    IAuthable
{
    /** @var PaymentMethodType */
    public mixed $paymentMethodType = PaymentMethodType::BNPL;

    /** @var BNPLType */
    public mixed $bnplType = null;

    /**
     * The endpoint to which the customer should be redirected after a payment has been attempted or
     * successfully completed on the payment scheme's site.
     *
     * @var string
     */
    public ?string $returnUrl = null;

    /**
     * The endpoint which will receive payment-status messages.
     * This will include the result of the transaction or any updates to the transaction status.
     * For certain asynchronous payment methods these notifications may come hours or
     * days after the initial authorization.
     *
     * @var string
     */
    public ?string $statusUpdateUrl = null;

    /**
     * The customer will be redirected back to your notifications.cancel_url in case the transaction is canceled
     *
     * @var string
     */
    public ?string $cancelUrl = null;

    public function __construct($bnplType)
    {
        $this->bnplType = $bnplType;
    }

    /**
     * Authorizes the payment method
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function authorize($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::AUTH, $this))
            ->withAmount($amount)
            ->withModifier(TransactionModifier::BAY_NOW_PAY_LATER);
    }

    /** @return PaymentMethodType */
    function getPaymentMethodType()
    {
        return PaymentMethodType::BNPL;
    }
}