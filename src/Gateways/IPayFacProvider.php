<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

interface IPayFacProvider
{
    
    /**
     * @throws ApiException
     * @return Transaction
     */
    public function processPayFac(PayFacBuilder $builder);
}
