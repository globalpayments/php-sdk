<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Entities\Action;
use GlobalPayments\Api\Entities\Reporting\BaseSummary;

class PagedResult extends BaseSummary
{
    public ?string $timeCreated = null;
    public ?object $paging = null;
    public ?string $accountId = null;
    public ?string $accountName = null;
    public ?int $totalRecordCount = null;
    public ?int $currentPageSize = null;
    public ?int $currentIndex = null;
    public ?string $timeRetrieved = null;
    public ?string $isLastPage = null;
    public ?int $page = null;
    public ?int $pageSize = null;
    public ?string $order = null;
    public ?string $orderBy = null;
    public ?object $filter = null;
    public ?Action $action = null;
    public array $result = [];
}