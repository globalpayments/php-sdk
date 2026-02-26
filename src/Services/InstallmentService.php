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
     * @param string $configName
     * @return Installment
     */
    public static function create(IInstallmentEntity $entity, string $configName = 'default'): Installment
    {
        $response = (new InstallmentBuilder(TransactionType::CREATE, $entity))
            ->execute($configName);
        return $response;
    }

    /**
     * Get installment details by ID
     * 
     * @param string $installmentId
     * @param string $configName
     * @return Installment
     */
    public static function get(string $installmentId, string $configName = 'default'): Installment
    {
        $builder = new InstallmentBuilder(TransactionType::FETCH);
        $builder->installmentId = $installmentId;
        return $builder->execute($configName);
    }
}