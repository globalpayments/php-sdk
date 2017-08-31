<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\RecurringBuilder;

interface IRecurringService
{
    public function processRecurring(RecurringBuilder $builder);
}
