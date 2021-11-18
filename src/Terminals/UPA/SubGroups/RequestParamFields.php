<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class RequestParamFields implements IRequestSubGroup
{
    /*
     * ID of the clerk if in retail mode, and ID of the server if in restaurant mode.
     */
    public $clerkId = null;
    
    /*
     * When enabled create token request is sent to Portico to generate a token for a cardholder
     * 
     * Possible values: 0 or 1
     */
    public $tokenRequest = null;
    
    /*
     * Token returned previously by the host.
     */
    public $tokenValue = null;
        
    /*
     * return Array
     */
    public function getElementString()
    {
        // Strip null values
        return array_filter((array) $this, function ($val) {
            return !is_null($val);
        });
    }
    
    public function setParams($builder)
    {
        if (!empty($builder->clerkId)) {
            $this->clerkId = $builder->clerkId;
        }
        
        if (!empty($builder->requestMultiUseToken)) {
            $this->tokenRequest = $builder->requestMultiUseToken;
        }
        
        if ($builder->paymentMethod != null &&
                    $builder->paymentMethod instanceof CreditCardData &&
                    !empty($builder->paymentMethod->token)
            ) {
            $this->tokenValue = $builder->paymentMethod->token;
        }
    }
}
