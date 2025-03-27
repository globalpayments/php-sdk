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
    public $accountId;

    /**
     * A meaningful label for the merchant account set by Global Payments
     * @var string
     */
    public $accountName;

    /**
     * @var string
     */
    public $merchantId;

    /**
     * The merchant's account id that will be receiving the transfer
     * @var string
     */
    public $recipientAccountId;

    /** @var string */
    public $recipientMerchantId;

    /** @var UsableBalanceMode */
    public $usableBalanceMode;


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