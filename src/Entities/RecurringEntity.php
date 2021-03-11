<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Services\RecurringService;

/**
 * Base implementation for recurring resource types.
 * </summary>
 */
abstract class RecurringEntity implements IRecurringEntity
{
    /**
     * All resource should be supplied a merchant-/application-defined ID.
     *
     * @var string
     */
    public $id;

    /**
     * All resources should be supplied a gateway-defined ID.
     *
     * @var string
     */
    public $key;

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        return RecurringService::create($this);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($force = false)
    {
        try {
            return RecurringService::delete($this, $force);
        } catch (ApiException $exc) {
            throw new ApiException('Failed to delete record, see inner exception for more details', $exc);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function find($id, $configName = 'default')
    {
        $client = ServicesContainer::instance()->getRecurringClient($configName);
        if (!$client->supportsRetrieval) {
            throw new UnsupportedTransactionException();
        }

        $identifier = static::getIdentifierName();
        $response = RecurringService::search(static::class)
            ->addSearchCriteria($identifier, $id)
            ->execute();

        foreach ($response as $entity) {
            if ($entity->id === (string) $id) {
                return RecurringService::get($entity);
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public static function findAll($configName = 'default')
    {
        $client = ServicesContainer::instance()->getRecurringClient($configName);
        if (!$client->supportsRetrieval) {
            throw new UnsupportedTransactionException();
        }

        return RecurringService::search(static::class)->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function saveChanges($configName = 'default')
    {
        try {
            return RecurringService::edit($this);
        } catch (ApiException $exc) {
            throw new ApiException('Update failed, see inner exception for more details', $exc);
        }
    }

    protected static function getIdentifierName()
    {
        if (static::class === Customer::class) {
            return 'customerIdentifier';
        } elseif (static::class === RecurringPaymentMethod::class) {
            return 'paymentMethodIdentifier';
        } elseif (static::class === Schedule::class) {
            return 'scheduleIdentifier';
        }
        return '';
    }
}
