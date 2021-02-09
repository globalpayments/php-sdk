<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Utils\AccessTokenInfo;

class ReportingFindSettlementTransactions
{
    public $page;
    public $page_size;
    public $order;
    public $order_by;
    public $number_first6;
    public $number_last4;
    public $deposit_status;
    public $account_name;
    public $brand;
    public $arn;
    public $brand_reference;
    public $authcode;
    public $reference;
    public $status;
    public $from_time_created;
    public $to_time_created;
    public $deposit_id;
    public $from_deposit_time_created;
    public $to_deposit_time_created;
    public $from_batch_time_created;
    public $to_batch_time_created;
    public $systemMid;
    public $systemHierarchy;

    public static function createFromTransactionReportBuilder(
        TransactionReportBuilder $builder,
        AccessTokenInfo $accessTokenInfo
    ) {
        $settleTrans = new ReportingFindSettlementTransactions();
        $settleTrans->page = $builder->page;
        $settleTrans->page_size = $builder->pageSize;
        $settleTrans->order = $builder->transactionOrder;
        $settleTrans->order_by = $builder->transactionOrderBy;
        $settleTrans->number_first6 = $builder->searchBuilder->cardNumberFirstSix;
        $settleTrans->number_last4 = $builder->searchBuilder->cardNumberLastFour;
        $settleTrans->deposit_status = $builder->searchBuilder->depositStatus;
        $settleTrans->account_name = $accessTokenInfo->getDataAccountName();
        $settleTrans->brand = $builder->searchBuilder->cardBrand;
        $settleTrans->arn = $builder->searchBuilder->aquirerReferenceNumber;
        $settleTrans->brand_reference = $builder->searchBuilder->brandReference;
        $settleTrans->authcode = $builder->searchBuilder->authCode;
        $settleTrans->reference = $builder->searchBuilder->referenceNumber;
        $settleTrans->status = $builder->searchBuilder->transactionStatus;
        $settleTrans->from_time_created = !empty($builder->searchBuilder->startDate) ? $builder->searchBuilder->startDate->format('Y-m-d') : null;
        $settleTrans->to_time_created = !empty($builder->searchBuilder->endDate) ?
            $builder->searchBuilder->endDate->format('Y-m-d') : null;
        $settleTrans->deposit_id = $builder->searchBuilder->depositId;
        $settleTrans->from_deposit_time_created = !empty($builder->searchBuilder->startDepositDate) ?
            $builder->searchBuilder->startDepositDate->format('Y-m-d') : null;
        $settleTrans->to_deposit_time_created = !empty($builder->searchBuilder->endDepositDate) ?
            $builder->searchBuilder->endDepositDate->format('Y-m-d') : null;
        $settleTrans->from_batch_time_created = !empty($builder->searchBuilder->startBatchDate) ?
            $builder->searchBuilder->startBatchDate->format('Y-m-d') : null;
        $settleTrans->to_batch_time_created = !empty($builder->searchBuilder->endBatchDate) ?
            $builder->searchBuilder->endBatchDate->format('Y-m-d') : null;
        $settleTrans->systemMid = $builder->searchBuilder->merchantId;
        $settleTrans->systemHierarchy = $builder->searchBuilder->systemHierarchy;

        $queryString = array();
        foreach ($settleTrans as $key => $value) {
            if (!empty($value)) {
                if ($key == 'systemMid') {
                    $queryString['system.mid'] = $value;
                    continue;
                }
                if ($key == 'systemHierarchy') {
                    $queryString['system.hierarchy'] = $value;
                    continue;
                }
                $queryString[$key] = $value;
            }
        }

        return $queryString;
    }
}