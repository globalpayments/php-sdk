<?php

namespace GlobalPayments\Api\Entities\Reporting;

final class TransactionList
{
    private array $list;

    public function __construct(TransactionSummary ...$transaction)
    {
        $this->list = $transaction;
    }

    public function add(TransactionSummary $transactionSummary) : void
    {
        $this->list[] = $transactionSummary;
    }

    public function all() : array
    {
        return $this->list;
    }
}