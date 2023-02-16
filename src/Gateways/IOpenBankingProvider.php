<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\AuthorizationBuilder;

interface IOpenBankingProvider
{
    public function processOpenBanking(AuthorizationBuilder $builder);
}