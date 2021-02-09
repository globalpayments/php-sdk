<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Utils\AccessTokenInfo;
use GlobalPayments\Api\Utils\StringUtils;

class ReportingFindDeposits
{
    public $page;
    public $page_size;
    public $order_by;
    public $order;
    public $account_name;
    public $from_time_created;
    public $to_time_created;
    public $id;
    public $status;
    public $amount;
    public $masked_account_number_last4;
    public $systemMid;
    public $systemHierarchy;

    public static function createFromTransactionReportBuilder(
        TransactionReportBuilder $builder,
        AccessTokenInfo $tokenInfo
    ) {
        $deposit = new ReportingFindDeposits();
        $deposit->page = $builder->page;
        $deposit->page_size = $builder->pageSize;
        $deposit->order_by = $builder->depositOrderBy;
        $deposit->order = $builder->depositOrder;
        $deposit->account_name = $tokenInfo->getDataAccountName();
        $deposit->from_time_created = !empty($builder->startDate) ?
            $builder->startDate->format('Y-m-d') : null;
        $deposit->to_time_created = !empty($builder->endDate) ?
            $builder->endDate->format('Y-m-d') : null;
        $deposit->id = $builder->searchBuilder->depositId;
        $deposit->status = $builder->searchBuilder->depositStatus;
        $deposit->amount = StringUtils::toNumeric($builder->searchBuilder->amount);
        $deposit->masked_account_number_last4 = $builder->searchBuilder->accountNumberLastFour;
        $deposit->systemMid = $builder->searchBuilder->merchantId;
        $deposit->systemHierarchy = $builder->searchBuilder->systemHierarchy;

        $queryString = array();
        foreach ($deposit as $key => $value) {
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