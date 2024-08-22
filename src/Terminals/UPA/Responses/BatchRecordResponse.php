<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Reporting\TransactionList;

class BatchRecordResponse
{
    public int $batchId;
    /** Batch Sequence Number */
    public int $batchSeqNbr;
    public string $batchStatus;

    public string $openUtcDateTime;
    /** Batch close date and time in UTC. */
    public ?string $closeUtcDateTime;
    /** The transaction identifier of the transaction that caused the batch to be opened.  */
    public string $openTnxId;
    public float $totalAmount;
    public ?int $totalCount;
    public ?int $creditCnt;
    public ?float $creditAmt;
    public ?int $debitCnt;
    public ?float $debitAmt;
    public ?int $saleCnt;
    public ?float $saleAmt;
    public ?int $returnCnt;
    public ?float $returnAmt;
    public ?float $totalGratuityAmt;
    /**
     *  The list of  records, which contains the number of transactions, and the total amount per transType.
     * @var BatchTransactionList
     */
    public BatchTransactionList $batchTransactions;

    /**
     * List which contains the details per transaction.
     * @var TransactionList
     */
    public TransactionList $transactionDetails;
}