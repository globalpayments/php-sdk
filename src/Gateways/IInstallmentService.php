<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\InstallmentBuilder;

interface IInstallmentService
{
    /**
     * @param InstallmentBuilder $builder
     */
    public function processInstallment(InstallmentBuilder $builder);
}
