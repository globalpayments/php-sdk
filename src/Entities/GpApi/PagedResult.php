<?php

namespace GlobalPayments\Api\Entities\GpApi;

class PagedResult
{
    public $totalRecordCount;
    public $page;
    public $pageSize;
    public $order;
    public $orderBy;
    public $result = [];
}