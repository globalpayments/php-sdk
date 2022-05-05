<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\BankPaymentBuilder;

interface IOpenBankingProvider
{
    public function processOpenBanking(BankPaymentBuilder $builder);
}