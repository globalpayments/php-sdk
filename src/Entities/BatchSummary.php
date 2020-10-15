<?php

namespace GlobalPayments\Api\Entities;

/**
 * Details a closed batch.
 */
class BatchSummary
{
    /**
     * The batch's ID.
     *
     * @var integer
     */
    public $id;

    /**
     * The batch's transaction count.
     *
     * @var integer
     */
    public $transactionCount;

    /**
     * The batch's total amount to be settled.
     *
     * @var float
     */
    public $totalAmount;

    /**
     * The batch's sequence number; where applicable.
     *
     * @var string
     */
    public $sequenceNumber;

    /** @var string */
    public $status;
}
