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
    public $cardHolderCurrency;

    /**
     * The amount to offer the cardholder.
     *
     * @var float|string|null
     */
    public $cardHolderAmount;

    /**
     * The exchange rate offered by Currency Conversion Processor for this transaction
     *
     * @var float|string|null
     */
    public $cardHolderRate;

    /**
     * The original currency sent in the request.
     *
     * @var string
     */
    public $merchantCurrency;

    /**
     * The original amount sent in the request.
     *
     * @var float|string|null
     */
    public $merchantAmount;

    /**
     * The foreign exchange markup.
     *
     * @var float|string
     */
    public $marginRatePercentage;

    /**
     * Source of the exchange rate.
     *
     * @var string
     */
    public $exchangeRateSourceName;

    /**
     * Currently not used
     *
     * @var float|string
     */
    public $commissionPercentage;

    /**
     * Timestamp of the exchange rate source.
     *
     * @var string
     */
    public $exchangeRateSourceTimestamp;
}
