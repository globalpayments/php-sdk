<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\ServicesContainer;

class FraudBuilder extends SecureBuilder
{
    public function __construct($transactionType)
    {
        parent::__construct();

        $this->authenticationSource = AuthenticationSource::BROWSER;
        $this->transactionType = $transactionType;
    }

    public function withPaymentMethod(IPaymentMethod $value)
    {
        $this->paymentMethod = $value;
        return $this;
    }

    public function execute($configName = 'default')
    {
        $client = ServicesContainer::instance()->getFraudCheckClient($configName);
        return $client->processFraud($this);
    }

    /** @return void */
    public function setupValidations()
    {
        $this->validations->of(TransactionType::RISK_ASSESS)
            ->check('paymentMethod')->isNotNull();
    }
}