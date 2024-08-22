<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

final class BatchTransactionList
{
    private array $list;

    public function __construct(BatchTransaction ...$transaction)
    {
        $this->list = $transaction;
    }

    public function add(BatchTransaction $transactionSummary) : void
    {
        $this->list[] = $transactionSummary;
    }

    public function all() : array
    {
        return $this->list;
    }
}