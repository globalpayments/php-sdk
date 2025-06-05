<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\InstallmentBuilder;
use GlobalPayments\Api\Entities\IInstallmentEntity;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Installment;

class InstallmentService
{
    /**
     * @param IInstallmentEntity $entity
     * @return Installment
     */
    public static function create(IInstallmentEntity $entity): Installment
    {
        $response = (new InstallmentBuilder(TransactionType::CREATE, $entity))
            ->execute();
        return $response;
    }
}