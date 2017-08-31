<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Entities\RecurringEntity;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class RecurringService
{
    public static function create(RecurringEntity $entity)
    {
        $response = (new RecurringBuilder(TransactionType::CREATE, $entity))
            ->execute();
        return $response;
    }

    public static function delete(RecurringEntity $entity, $force = false)
    {
        $response = (new RecurringBuilder(TransactionType::DELETE, $entity))
            ->execute();
        return $response;
    }

    public static function edit(RecurringEntity $entity)
    {
        $response = (new RecurringBuilder(TransactionType::EDIT, $entity))
            ->execute();
        return $response;
    }

    public static function get($entity)
    {
        $response = (new RecurringBuilder(TransactionType::FETCH, $entity))
            ->execute();
        return $response;
    }

    public static function search($entityType)
    {
        return new RecurringBuilder(TransactionType::SEARCH, new $entityType());
    }
}
