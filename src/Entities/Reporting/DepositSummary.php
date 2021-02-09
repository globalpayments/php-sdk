<?php


namespace GlobalPayments\Api\Entities\Reporting;


class DepositSummary
{
    /**
     * @var string
     */
    public $depositId;
    /**
     * @var string
     */
    public $merchantHierarchy;
    /**
     * @var string
     */
    public $merchantName;
    /**
     * @var string
     */
    public $merchantDbaName;
    /**
     * @var string
     */
    public $merchantNumber;
    /**
     * @var string
     */
    public $merchantCategory;
    /**
     * @var \DateTime
     */
    public $depositDate;
    /**
     * @var string
     */
    public $reference;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
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