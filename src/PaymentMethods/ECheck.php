<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\{Address, Customer, Transaction};
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    PaymentMethodUsageMode,
    TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\Interfaces\{
    IChargable,
    IPaymentMethod,
    ITokenizable,
    IVerifyable
};

class ECheck implements
    IPaymentMethod,
    IChargable,
    IVerifyable,
    ITokenizable
{
    public $accountNumber;
    public $accountType;
    public $achVerify;
    public $birthYear;
    public $branchTransitNumber;
    public $checkHolderName;
    public $checkNumber;
    public $checkType;
    public $checkVerify;
    public $driversLicenseNumber;
    public $driversLicenseState;
    public $entryMode;
    public $financialInstitutionNumber;
    public $micrNumber;
    public $paymentMethodType = PaymentMethodType::ACH;
    public $phoneNumber;
    public $routingNumber;
    public $secCode;
    public $ssnLast4;
    public $token;
    public $checkReference;
    public $merchantNotes;
    public $bankName;
    /**
     * @var Address
     */
    public $bankAddress;

    /**
     * @var Customer
     */
    public $customer;

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
            ->withAmount($amount);
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
     * Verifies the payment method
     *
     * @return AuthorizationBuilder
     */
    public function verify() : AuthorizationBuilder
    {
        return new AuthorizationBuilder(TransactionType::VERIFY, $this);
    }

    /** @return PaymentMethodType */
    function getPaymentMethodType()
    {
        return $this->paymentMethodType;
    }

    public function tokenize(bool $validateCard = true, string $configName = 'default'): string
    {
        /** @var TransactionType */
        $type = $validateCard ? TransactionType::VERIFY : TransactionType::TOKENIZE;

        $builder = new AuthorizationBuilder($type, $this);
        $response = $builder->withRequestMultiUseToken(true)
            ->execute($configName);

        return $response->token;
    }

    public function tokenizeWithCustomerData(
        bool $validateCard, 
        Address $billingAddress,
        Customer $customerData,
        string $configName = 'default'
    ): string {

        /** @var TransactionType */
        $type = $validateCard ? TransactionType::VERIFY : TransactionType::TOKENIZE;

        $builder = new AuthorizationBuilder($type, $this);
        $builder = $builder->withRequestMultiUseToken($validateCard)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE);

        if ($billingAddress !== null) {
            $builder = $builder->withAddress($billingAddress);
        }
        if ($customerData !== null) {
            $builder = $builder->withCustomerData($customerData);
        }

        $response = $builder->execute($configName);
        return $response->token;
    }

    /**
     * Gets token information for the specified token
     * @param string $configName
     * 
     * @return Transaction
     */
    public function getTokenInformation(string $configName = 'default'): Transaction
    {
        $builder = new AuthorizationBuilder(
            TransactionType::GET_TOKEN_INFO,
            $this
        );

        return $builder->execute($configName);
    }

    public function updateTokenExpiry() {
        throw new UnsupportedTransactionException();
    }

    public function deleteToken() {}

    public function detokenize()
    {
        throw new UnsupportedTransactionException();
    }

    public function updateToken()
    {
        throw new UnsupportedTransactionException();
    }
}
