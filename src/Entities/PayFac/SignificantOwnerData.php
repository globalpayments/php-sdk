<?php

namespace GlobalPayments\Api\Entities\PayFac;

class SignificantOwnerData
{
    /**
     * Seller’s Authorized Signer First Name. By default Merchant’s First name is saved
     *
     * @var string
     */
    public $authorizedSignerFirstName;

    /**
     * Seller’s Authorized Signer Last Name. By default Merchant’s Last name is saved
     *
     * @var string
     */
    public $authorizedSignerLastName;
    
    /**
     * This field contains the Seller’s Authorized Signer Title
     *
     * @var string
     */
    public $authorizedSignerTitle;
    
    public $significantOwnerData;
    
    public function __construct()
    {
        $this->significantOwnerData = new OwnersData();
    }
}
