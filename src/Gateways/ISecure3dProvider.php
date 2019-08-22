<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

interface ISecure3dProvider
{
    /** @return Secure3dVersion */
    public function getVersion();

    /**
     * @throws ApiException
     * @return Transaction
     */
    public function processSecure3d(Secure3dBuilder $builder);
}
