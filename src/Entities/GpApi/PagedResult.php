<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Entities\Action;
use GlobalPayments\Api\Entities\Reporting\BaseSummary;

class PagedResult extends BaseSummary
{
    public $accountId;
    public $accountName;
    public $totalRecordCount;
    public $currentPageSize;
    public $page;
    public $pageSize;
    public $order;
    public $orderBy;
    public $accountId;
    public $accountName;
    public ?object $filter = null;
    public ?Action $action = null;
    public $result = [];
}