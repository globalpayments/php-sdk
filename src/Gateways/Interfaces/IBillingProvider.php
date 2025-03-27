<?php

namespace GlobalPayments\Api\Gateways\Interfaces;

use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\BillingResponse;

interface IBillingProvider
{
    public function isBillDataHosted(): bool;

    public function processBillingRequest(BillingBuilder $builder): BillingResponse;
}