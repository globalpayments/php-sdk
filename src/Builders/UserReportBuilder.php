<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\MerchantAccountsSortProperty;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;

class UserReportBuilder extends ReportBuilder
{
    /**
     * @var SortDirection
     */
    public mixed $order = null;

    /** @var MerchantAccountsSortProperty */
    public mixed $accountOrderBy = null;

    /**
     * @internal
     * @var string
     */
    public ?string $transactionType = null;

    /**
     * @internal
     * @var SearchCriteriaBuilder
     */
    public mixed $searchBuilder = null;

    public mixed $transactionModifier = TransactionModifier::NONE;

    public function __construct($reportType)
    {
        parent::__construct($reportType);

        $this->transactionType = $reportType;
        $this->searchBuilder = new SearchCriteriaBuilder($this);
    }

    protected function setupValidations()
    {
        // TODO: Implement setupValidations() method.
    }

    public function withModifier($transactionModifier)
    {
        $this->transactionModifier = $transactionModifier;

        return $this;
    }

    public function withAccountId($accountId)
    {
        $this->searchBuilder->accountId = $accountId;

        return $this;
    }

    /**
     * Set the gateway order for the criteria
     * @param string $sortProperty sorting property
     * @param string $sortDirection sorting direction
     * @return $this
     */
    public function orderBy($sortProperty, $sortDirection = SortDirection::DESC)
    {
        $this->order = $sortDirection;
        switch ($this->reportType) {
            case ReportType::FIND_ACCOUNTS_PAGED:
            case ReportType::FIND_MERCHANTS_PAGED:
                $this->accountOrderBy = $sortProperty;
                break;
            default:
                throw new \InvalidArgumentException("Invalid order found");
        }

        return $this;
    }
}