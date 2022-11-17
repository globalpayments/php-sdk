<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;

class UserReportBuilder extends ReportBuilder
{
    /**
     * @var SortDirection
     */
    public $order;

    /**
     * @internal
     * @var string
     */
    public $transactionType;

    public $transactionModifier = TransactionModifier::NONE;

    public function __construct($reportType)
    {
        parent::__construct($reportType);

        $this->transactionType = $reportType;
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
}