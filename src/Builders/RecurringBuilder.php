<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\IRecurringEntity;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class RecurringBuilder extends TransactionBuilder
{
    /**
     * @internal
     * @var string
     */
    public $key;

    /**
     * @internal
     * @var string
     */
    public $orderId;

    /**
     * @internal
     * @var IRecurringEntity
     */
    public $entity;

    /**
     * @internal
     * @var array<string,string>
     */
    public $searchCriteria;

    /**
     * @param TransactionType $type
     * @param IRecurringEntity $entity
     *
     * @return
     */
    public function __construct($type, IRecurringEntity $entity = null)
    {
        parent::__construct($type);

        $this->searchCriteria = [];
        if ($entity !== null) {
            $this->entity = $entity;
            $this->key = $entity->key;
        }
    }

    /**
     * @internal
     * @param string $key
     * @param string $value
     *
     * @return RecurringBuilder
     */
    public function addSearchCriteria($key, $value)
    {
        $this->searchCriteria[$key] = $value;
        return $this;
    }

    /**
     * Executes the builder against the gateway.
     *
     * @return mixed
     */
    public function execute($configName = 'default')
    {
        parent::execute($configName);

        $client = ServicesContainer::instance()->getRecurringClient($configName);
        return $client->processRecurring($this);
    }

    protected function setupValidations()
    {
        $this->validations->of(
            TransactionType::EDIT |
            TransactionType::DELETE |
            TransactionType::FETCH
        )
            ->check('key')->isNotNull();

        $this->validations->of(
            TransactionType::SEARCH
        )
            ->check('searchCriteria')->isNotNull();
    }
}
