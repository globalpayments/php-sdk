<?php


namespace GlobalPayments\Api\Entities\Reporting;


class DepositSummary extends BaseSummary
{
    /**
     * @var string
     */
    public $depositId;
    /**
     * @var \DateTime
     */
    public $depositDate;
    /**
     * @var string
     */
    public $reference;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $routingNumber;
    /**
     * @var string
     */
    public $accountNumber;
    /**
     * @var string
     */
    public $mode;
    /**
     * @var string
     */
    public $summaryModel;
    /**
     * @var integer
     */
    public $salesTotalCount;
    /**
     * @var float
     */
    public $salesTotalAmount;
    /**
     * @var string
     */
    public $salesTotalCurrency;
    /**
     * @var integer
     */
    public $refundsTotalCount;
    /**
     * @var float
     */
    public $refundsTotalAmount;
    /**
     * @var string
     */
    public $refundsTotalCurrency;
    /**
     * @var integer
     */
    public $chargebackTotalCount;
    /**
     * @var float
     */
    public $chargebackTotalAmount;
    /**
     * @var string
     */
    public $chargebackTotalCurrency;
    /**
     * @var integer
     */
    public $representmentTotalCount;
    /**
     * @var float
     */
    public $representmentTotalAmount;
    /**
     * @var string
     */
    public $representmentTotalCurrency;
    /**
     * @var float
     */
    public $feesTotalAmount;
    /**
     * @var string
     */
    public $feesTotalCurrency;
    /**
     * @var integer
     */
    public $adjustmentTotalCount;
    /**
     * @var float
     */
    public $adjustmentTotalAmount;
    /**
     * @var string
     */
    public $adjustmentTotalCurrency;
    /**
     * @var string
     */
    public $status;
}