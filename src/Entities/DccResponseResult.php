<?php

namespace GlobalPayments\Api\Entities;

/**
 * DCC response data
 */
class DccResponseResult
{
    /**
     * The currency of the cardholder
     *
     * @var string|null
     */
    public $cardholdercurrency;

    /**
     * The amount to offer the cardholder.
     *
     * @var float|string|null
     */
    public $cardholderamount;

    /**
     * The exchange rate offered by Currency Conversion Processor for this transaction
     *
     * @var float|string|null
     */
    public $cardholderrate;

    /**
     * The original currency sent in the request.
     *
     * @var string
     */
    public $merchantcurrency;

    /**
     * The original amount sent in the request.
     *
     * @var float|string|null
     */
    public $merchantamount;

    /**
     * The foreign exchange markup.
     *
     * @var float|string
     */
    public $marginratepercentage;

    /**
     * Source of the exchange rate.
     *
     * @var string
     */
    public $exchangeratesourcename;

    /**
     * Currently not used
     *
     * @var float|string
     */
    public $commissionpercentage;

    /**
     * Timestamp of the exchange rate source.
     *
     * @var string
     */
    public $exchangeratesourcetimestamp;

}

