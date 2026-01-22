<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\UsableBalanceMode;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class FundsAccount implements IPaymentMethod
{

    /**
     * A unique identifier for the merchant account set by Global Payments
     * @var string
     */
    public ?string $accountId = null;

    /**
     * A meaningful label for the merchant account set by Global Payments
     * @var string
     */
    public ?string $accountName = null;

    /**
     * @var string
     */
    public ?string $merchantId = null;

    /**
     * The merchant's account id that will be receiving the transfer
     * @var string
     */
    public ?string $recipientAccountId = null;

    /** @var string */
    public ?string $recipientMerchantId = null;

    /** @var UsableBalanceMode */
    public mixed $usableBalanceMode = null;


    public function transfer($amount)
    {
        return (new AuthorizationBuilder(TransactionType::TRANSFER_FUNDS, $this))
            ->withAmount($amount);
    }

    /** @return PaymentMethodType */
    function getPaymentMethodType()
    {
        return PaymentMethodType::ACCOUNT_FUNDS;
    }
}