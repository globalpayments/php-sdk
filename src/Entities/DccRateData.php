<?php

namespace GlobalPayments\Api\Entities;

/**
 * DCC data
 */
class DccRateData
{
    /**
     * The amount
     *
     * @var float|string|null
     */
    public $amount;
    
    /**
     * The currency
     *
     * @var string
     */
    public $currency;
    
    /**
     * The dccProcessor
     *
     * @var string
     */
    public $dccProcessor;
    
    /**
     * The dccRate
     *
     * @var string
     */
    public $dccRate;
    
    /**
     * The dccRateType
     *
     * @var string
     */
    public $dccRateType;
    
    /**
     * The dccType
     *
     * @var string
     */
    public $dccType;
    
    /**
     * The orderId
     *
     * @var string
     */
    public $orderId;
}
