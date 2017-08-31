<?php

namespace GlobalPayments\Api\Entities;

/**
 * Base interface for recurring resource types.
 */
interface IRecurringEntity
{
    /**
     * Creates a resource
     *
     * @return mixed
     */
    public function create();

    /**
     * Delete a record from the gateway.
     *
     * @param bool $force Indicates if the deletion should be forced. Default is `false`.
     *
     * @throws ApiException Thrown when the record cannot be deleted.
     * @return mixed
     */
    public function delete($force = false);

    /**
     * Searches for a specific record by `id`.
     *
     * @param $id The ID of the record to find
     *
     * @throws UnsupportedTransactionException Thrown when gateway does not support retrieving recurring records.
     * @return mixed|null If the record cannot be found, `null` is returned.
     */
    public static function find($id);

    /**
     * Lists all records of type `TResult`.
     *
     * @throws UnsupportedTransactionException Thrown when gateway does not support retrieving recurring records.
     * @return array<mixed>
     */
    public static function findAll();

    /**
     * The current record should be updated.
     *
     * Any modified properties will be persisted with the gateway.
     *
     * @throws ApiException Thrown when the record cannot be updated.
     * @return
     */
    public function saveChanges();
}
