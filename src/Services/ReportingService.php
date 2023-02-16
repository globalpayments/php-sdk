<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Builders\UserReportBuilder;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;

class ReportingService
{
    public static function findTransactions($transactionId = null)
    {
        return (new TransactionReportBuilder(ReportType::FIND_TRANSACTIONS))
            ->withTransactionId($transactionId);
    }

    public static function findDeposits()
    {
        return new TransactionReportBuilder(ReportType::FIND_DEPOSITS);
    }

    public static function findSettlementTransactions()
    {
        return new TransactionReportBuilder(ReportType::FIND_SETTLEMENT_TRANSACTIONS);
    }

    public static function activity()
    {
        return (new TransactionReportBuilder(ReportType::ACTIVITY));
    }

    public static function transactionDetail(?string $transactionId)
    {
        return (new TransactionReportBuilder(ReportType::TRANSACTION_DETAIL))
            ->withTransactionId($transactionId);
    }

    public static function depositDetail($depositId)
    {
        return (new TransactionReportBuilder(ReportType::DEPOSIT_DETAIL))
            ->withDepositId($depositId);
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

    public static function findStoredPaymentMethodsPaged($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_STORED_PAYMENT_METHODS_PAGED))
            ->withPaging($page, $pageSize);
    }

    public static function storedPaymentMethodDetail($storedPaymentMethodId)
    {
        return (new TransactionReportBuilder(ReportType::STORED_PAYMENT_METHOD_DETAIL))
            ->withStoredPaymentMethodId($storedPaymentMethodId);
    }

    public static function findActionsPaged($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_ACTIONS_PAGED))
            ->withPaging($page, $pageSize);
    }

    public static function actionDetail($actionId)
    {
        return (new TransactionReportBuilder(ReportType::ACTION_DETAIL))
            ->withActionId($actionId);
    }

    public static function documentDisputeDetail($disputeId)
    {
        return (new TransactionReportBuilder(ReportType::DOCUMENT_DISPUTE_DETAIL))
            ->withDisputeId($disputeId);
    }

    public static function bankPaymentDetail($id)
    {
        return (new TransactionReportBuilder(ReportType::FIND_BANK_PAYMENT))
            ->withBankPaymentId($id);
    }

    public static function findBankPaymentTransactions($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_BANK_PAYMENT))
            ->withPaging($page, $pageSize);
    }

    public static function findMerchants($page, $pageSize)
    {
        return (new UserReportBuilder(ReportType::FIND_MERCHANTS_PAGED))
            ->withModifier(TransactionModifier::MERCHANT)
            ->withPaging($page, $pageSize);
    }
}
