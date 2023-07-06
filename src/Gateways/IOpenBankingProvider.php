<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;

interface IOpenBankingProvider
{
    public function processOpenBanking(AuthorizationBuilder $builder);

    public function manageOpenBanking(ManagementBuilder $builder);
}