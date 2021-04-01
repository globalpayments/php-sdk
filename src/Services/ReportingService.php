<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Enums\ReportType;

class ReportingService
{
    public static function findTransactions($transactionId = null)
    {
        $response = (new TransactionReportBuilder(ReportType::FIND_TRANSACTIONS))
            ->withTransactionId($transactionId);
        return $response;
    }

    public static function findDeposits()
    {
        $response = new TransactionReportBuilder(ReportType::FIND_DEPOSITS);
        return $response;
    }

    public static function findSettlementTransactions()
    {
        $response = new TransactionReportBuilder(ReportType::FIND_SETTLEMENT_TRANSACTIONS);
        return $response;
    }

    public static function activity()
    {
        $response = (new TransactionReportBuilder(ReportType::ACTIVITY));
        return $response;
    }

    public static function transactionDetail($transactionId)
    {
        $response = (new TransactionReportBuilder(ReportType::TRANSACTION_DETAIL))
            ->withTransactionId($transactionId);
        return $response;
    }

    public static function depositDetail($depositId)
    {
        $response = (new TransactionReportBuilder(ReportType::DEPOSIT_DETAIL))
            ->withDepositId($depositId);
        return $response;
    }

    public static function findDisputes()
    {
        return new TransactionReportBuilder(ReportType::FIND_DISPUTES);
    }

    public static function disputeDetail($disputeId)
    {
        return (new TransactionReportBuilder(ReportType::DISPUTE_DETAIL))
            ->withDisputeId($disputeId);
    }

    public static function findSettlementDisputes()
    {
        return new TransactionReportBuilder(ReportType::FIND_SETTLEMENT_DISPUTES);
    }

    public static function settlementDisputeDetail($settlementDisputeId)
    {
        return (new TransactionReportBuilder(ReportType::SETTLEMENT_DISPUTE_DETAIL))
            ->withSettlementDisputeId($settlementDisputeId);
    }

    public static function findTransactionsPaged($page, $pageSize, $transactionId = null)
    {
        return (new TransactionReportBuilder(ReportType::FIND_TRANSACTIONS_PAGED))
            ->withTransactionId($transactionId)
            ->withPaging($page, $pageSize);
    }

    public static function findSettlementTransactionsPaged($page, $pageSize, $transactionId = null)
    {
        return (new TransactionReportBuilder(ReportType::FIND_SETTLEMENT_TRANSACTIONS_PAGED))
            ->withTransactionId($transactionId)
            ->withPaging($page, $pageSize);
    }

    public static function findDepositsPaged($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_DEPOSITS_PAGED))
            ->withPaging($page, $pageSize);
    }

    public static function findDisputesPaged($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_DISPUTES_PAGED))
            ->withPaging($page, $pageSize);
    }

    public static function findSettlementDisputesPaged($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_SETTLEMENT_DISPUTES_PAGED))
            ->withPaging($page, $pageSize);
    }
}