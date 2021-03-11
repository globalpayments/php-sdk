<?php

namespace GlobalPayments\Api\Entities\PayFac;

class SingleSignOnData
{
    /**
     * The ProPay system requires that your single-sign-on originate from the URL originally provided here.
     *
     * @var string
     */
    public $referrerUrl;

    /**
     * The ProPay system requires that your signle sign-on originate from the URL originally provided here.
     * Can supply a range of class c or more restrictive.
     *
     * @var string
     */
    public $ipAddress;
    
    /**
     * The ProPay system requires that your signle sign-on originate from the URL originally provided here.
     * Can supply a range of class c or more restrictive.
     *
     * @var string
     */
    public $ipSubnetMask;
}
