<?php

namespace GlobalPayments\Api\Entities\PayFac;

class SignificantOwnerData
{
    /**
     * Sellerís Authorized Signer First Name. By default Merchantís First name is saved
     *
     * @var string
     */
    public $authorizedSignerFirstName;

    /**
     * Sellerís Authorized Signer Last Name. By default Merchantís Last name is saved
     *
     * @var string
     */
    public $authorizedSignerLastName;
    
    /**
     * This field contains the Sellerís Authorized Signer Title
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
