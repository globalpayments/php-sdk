<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\FraudBuilder;
use GlobalPayments\Api\Entities\RiskAssessment;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;

interface IFraudCheckService
{
    public function processFraud(FraudBuilder $builder) : RiskAssessment;
}