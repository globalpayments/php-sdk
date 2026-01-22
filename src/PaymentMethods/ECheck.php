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
    public ?string $accountNumber = null;
    public mixed $accountType = null;
    public ?bool $achVerify = null;
    public string|int|null $birthYear = null;
    public ?string $branchTransitNumber = null;
    public ?string $checkHolderName = null;
    public ?string $checkNumber = null;
    public mixed $checkType = null;
    public ?bool $checkVerify = null;
    public ?string $driversLicenseNumber = null;
    public ?string $driversLicenseState = null;
    public mixed $entryMode = null;
    public ?string $financialInstitutionNumber = null;
    public ?string $micrNumber = null;
    public mixed $paymentMethodType = PaymentMethodType::ACH;
    public ?string $phoneNumber = null;
    public ?string $routingNumber = null;
    public ?string $secCode = null;
    public ?string $ssnLast4 = null;
    public ?string $token = null;
    public ?string $checkReference = null;
    public ?string $merchantNotes = null;
    public ?string $bankName = null;
    /**
     * @var Address
     */
    public ?Address $bankAddress = null;

    /**
     * @var Customer
     */
    public ?Customer $customer = null;

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
