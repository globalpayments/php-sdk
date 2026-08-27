<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Entities\Reporting\BaseSummary;

class PagedResult extends BaseSummary
{
    public $totalRecordCount;
    public $currentPageSize;
    public $page;
    public $pageSize;
    public $order;
    public $orderBy;
    public $filter;
    public $action;
    public $result = [];
}