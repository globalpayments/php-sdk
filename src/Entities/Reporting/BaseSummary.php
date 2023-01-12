<?php


namespace GlobalPayments\Api\Entities\Reporting;


abstract class BaseSummary
{
    /**
     * @var integer
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $merchantId;

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
}