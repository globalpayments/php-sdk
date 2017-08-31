<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;

class TransactionReportBuilder extends ReportBuilder
{
    /**
     * @internal
     * @var string
     */
    public $deviceId;

    /**
     * @internal
     * @var DateTime
     */
    public $endDate;

    /**
     * @internal
     * @var DateTime
     */
    public $startDate;

    /**
     * @internal
     * @var string
     */
    public $transactionId;

    /**
     * Sets the device ID as criteria for the report.
     *
     * @param string $value The device ID
     *
     * @return TransactionReportBuilder
     */
    public function withDeviceId($value)
    {
        $this->deviceId = $value;
        return $this;
    }

    /**
     * Sets the end date as criteria for the report.
     *
     * @param DateTime $value The end date
     *
     * @return TransactionReportBuilder
     */
    public function withEndDate($value)
    {
        $this->endDate = $value;
        return $this;
    }

    /**
     * Sets the start date as criteria for the report.
     *
     * @param DateTime $value The start date
     *
     * @return TransactionReportBuilder
     */
    public function withStartDate($value)
    {
        $this->startDate = $value;
        return $this;
    }

    /**
     * Sets the transaction ID as criteria for the report.
     *
     * @param string $value The transaction ID
     *
     * @return TransactionReportBuilder
     */
    public function withTransactionId($value)
    {
        $this->transactionId = $value;
        return $this;
    }

    protected function setupValidations()
    {
        $this->validations->of(ReportType::TRANSACTION_DETAIL)
            ->check('transactionId')->isNotNull()
            ->check('deviceId')->isNotNull()
            ->check('startDate')->isNotNull()
            ->check('endDate')->isNotNull();

        $this->validations->of(ReportType::ACTIVITY)
            ->check('transactionId')->isNull();
    }
}
