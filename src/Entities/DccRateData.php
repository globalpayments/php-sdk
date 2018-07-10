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
     * The name of the CCP (Currency Conversion Processor) the request is to be sent to
     *
     * @var string
     */
    public $dccProcessor;
    
    /**
     * Rate Offered for the Exchange
     *
     * @var string
     */
    public $dccRate;
    
    /**
     * Rate type, 'S' for authorisation transactions (Sale). 'R' for Refunds.
     *
     * @var string
     */
    public $dccRateType;
    
    /**
     * The type of currency conversion rate obtained. This is usually set to 1 but can contain other values.
     * Please consult with your Currency Conversion Processor.
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
