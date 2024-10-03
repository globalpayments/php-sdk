<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Entities\Reporting\BaseSummary;

class PagedResult extends BaseSummary
{
    public $totalRecordCount;
    public $page;
    public $pageSize;
    public $order;
    public $orderBy;
    public $result = [];
}