<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Address;

class BusinessData
{
    /**
     * The legal name of the business as registered.
     *
     * @var string
     */
    public $businessLegalName;

    /**
     * This field can be used to provide DBA information on an account. ProPay accounts can be
       configured to display DBA on cc statements. (Note most banks’ CC statements allow for 29
       characters so 255 max length is not advised.)
     *
     * @var string
     */
    public $doingBusinessAs;
    
    /**
     * Employer Identification Number can be added to a ProPay account. Must be 9 characters without dashes.
     *
     * @var string
     */
    public $employerIdentificationNumber;
    
    /**
     * Merchant Category Code
     *
     * @var string
     */
    public $merchantCategoryCode;
    
    /**
     * The Business’ website URL
     *
     * @var string
     */
    public $websiteURL;
    
    /**
     * The Business description
     *
     * @var string
     */
    public $businessDescription;
    
    /**
     * The monthly volume of bank card transactions; Value representing the number of pennies in USD, or the number
     * of [currency] without decimals. Defaults to $1000.00 if not sent
     *
     * @var int
     */
    public $monthlyBankCardVolume;
    
    /**
     * The average amount of an individual transaction; Value representing the number of pennies in
     * USD, or the number of [currency] without decimals. Defaults to $300.00 if not sent
     *
     * @var int
     */
    public $averageTicket;
    
    /**
     * The highest transaction amount; Value representing the number of pennies in USD, or the number
     * of [currency] without decimals. Defaults to $300.00 if not sent.
     *
     * @var int
     */
    public $highestTicket;
    
    /**
     * Business Physical Address
     *
     * @var GlobalPayments\Api\Entities\Address
     */
    public $businessAddress;
    
    
    public function __construct()
    {
        $this->businessAddress = new Address();
    }
}
