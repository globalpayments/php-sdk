<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Utils\StringUtils;

class ReportingFindTransactions
{
    public $page;
    public $page_size;
    public $order_by;
    public $order;
    public $id;
    public $type;
    public $channel;
    public $amount;
    public $currency;
    public $number_first6;
    public $number_last4;
    public $token_first6;
    public $token_last4;
    public $account_name;
    public $brand;
    public $brand_reference;
    public $authcode;
    public $reference;
    public $status;
    public $from_time_created;
    public $to_time_created;
    public $country;
    public $batch_id;
    public $entry_mode;
    public $name;

    public static function createFromTransactionReportBuilder(TransactionReportBuilder $builder)
    {
        $report = new ReportingFindTransactions();
        $report->page = $builder->page;
        $report->page_size = $builder->pageSize;
        $report->order_by = $builder->transactionOrderBy;
        $report->order = $builder->transactionOrder;
        $report->id = $builder->transactionId;
        $report->type = $builder->searchBuilder->paymentType;
        $report->channel = $builder->searchBuilder->channel;
        $report->amount = StringUtils::toNumeric($builder->searchBuilder->amount);
        $report->currency = $builder->searchBuilder->currency;
        $report->number_first6 = $builder->searchBuilder->cardNumberFirstSix;
        $report->number_last4 = $builder->searchBuilder->cardNumberLastFour;
        $report->token_first6 = $builder->searchBuilder->tokenFirstSix;
        $report->token_last4 = $builder->searchBuilder->tokenLastFour;
        $report->account_name = $builder->searchBuilder->accountName;
        $report->brand = $builder->searchBuilder->cardBrand;
        $report->brand_reference = $builder->searchBuilder->brandReference;
        $report->authcode = $builder->searchBuilder->authCode;
        $report->reference = $builder->searchBuilder->referenceNumber;
        $report->status = $builder->searchBuilder->transactionStatus;
        $report->from_time_created = !empty($builder->searchBuilder->startDate) ?
            $builder->searchBuilder->startDate->format('Y-m-d') : null;
        $report->to_time_created = !empty($builder->searchBuilder->endDate) ?
            $builder->searchBuilder->endDate->format('Y-m-d') : (new \DateTime())->format('Y-m-d');
        $report->country = $builder->searchBuilder->country;
        $report->batch_id = $builder->searchBuilder->batchId;
        $report->entry_mode = $builder->searchBuilder->paymentEntryMode;
        $report->name = $builder->searchBuilder->name;

        $queryString = array();
        foreach ($report as $name => $value) {
            if (!empty($value)) {
                $queryString[$name] = $value;
            }
        }

        return $queryString;
    }
}