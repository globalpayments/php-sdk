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
}
