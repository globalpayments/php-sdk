<?php

namespace GlobalPayments\Api\Entities;

/**
 * Fraud Management response data
 */
class FraudManagementResponse
{
    /**
     * This element indicates the mode the Fraud Filter executed in
     *
     * @var string
     */
    public $fraudResponseMode;
    
    /**
     * This field is used to determine what the overall result the Fraud Filter returned
     *
     * @var string
     */
    public $fraudResponseResult;
    
    /**
     * Filter rules
     *
     * @var array
     */
    public $fraudResponseRules;
}
