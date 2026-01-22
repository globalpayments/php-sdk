<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\IInstallmentEntity;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Installment;

class InstallmentBuilder extends TransactionBuilder 
{
    /**
     * @internal
     * @var IInstallmentEntity
     */
    public mixed $entity = null;

    /**
     * @internal
     * @var string
     */
    public ?string $key = null;

    /**
     * @param TransactionType $type
     * @param null|IInstallmentEntity $entity
     *
     * @return
     */
    public function __construct($type, ?IInstallmentEntity $entity = null)
    {
        parent::__construct($type);

        if ($entity !== null) {
            $this->entity = $entity;
        }
    }

    /**
     * @param string $configName
     * @return Installment
    */
    public function execute(string $configName = 'default'): Installment
    {
        parent::execute($configName);

        $client = ServicesContainer::instance()->getInstallmentClient($configName);
        return $client->processInstallment($this);
    }

    protected function setupValidations(){}
}