<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Address;

class OwnersData
{
    /**
     * This field contains the Title.
     *
     * @var string
     */
    public $title;

    /**
     * Owner First Name
     *
     * @var string
     */
    public $firstName;
    
    /**
     * Owner Last Name
     *
     * @var string
     */
    public $lastName;
    
    /**
     * Owner Email ID
     *
     * @var string
     */
    public $email;
    
    /**
     * Date of Birth of the Owner. Must be in ‘mm-dd-yyyy’ format.
     *
     * @var string
     */
    public $dateOfBirth;
    
    /**
     * Owner SSN ID
     *
     * @var string
     */
    public $ssn;
    
    /**
     * Percentage stake in company by owner. Must be whole number between 0 and 100.
     *
     * @var string
     */
    public $percentage;
    public $phone;
    
    /**
     *  address where Owner resides
     *
     * @var GlobalPayments\Api\Entities\Address
     */
    public $ownerAddress;
            
    public function __construct()
    {
        $this->ownerAddress = new Address();
    }
}
