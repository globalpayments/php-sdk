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

    public ?string $merchantId;

    public ?string $merchantHierarchy;

    public ?string $merchantName;
    public ?string $merchantDbaName;
    public ?string $merchantDeviceIdentifier;
}