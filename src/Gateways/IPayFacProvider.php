<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\User;

interface IPayFacProvider
{
    /**
     * @throws ApiException
     * @return Transaction
     */
    public function processPayFac(PayFacBuilder $builder);

    /**
     * @param PayFacBuilder $builder
     * @return User
     */
    public function processBoardingUser(PayFacBuilder $builder) : User;
}
