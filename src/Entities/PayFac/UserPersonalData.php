<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Address;

class UserPersonalData
{
    /**
     * Merchant/Individual first names.
     *
     * @var string
     */
    public $firstName;

    /**
     * Merchant/Individual middle initial.
     *
     * @var string
     */
    public $mInitial;
    
    /**
     * Merchant/Individual last name.
     *
     * @var string
     */
    public $lastName;
    
    /**
     * Merchant/Individual Date of birth. Must be in ‘mm-dd-yyyy’ format. *Individual must be 18+ to obtain an
        account. The value 01-01-1981 will give a successful response. All others will return a Status 66 (Failed KYC)
     *
     * @var string
     */
    public $dateOfBirth;
    
    
    /**
     * Merchant/Individual last name.
     *
     * @var string
     */
    public $ssn;
    
    /**
     * Merchant/Individual email address. Must be unique in ProPay system. *ProPay’s system will send automated
        emails to the email address on file unless NotificationEmail is provided.
       *Truncated, if value provided is greater than max value
     *
     * @var string
     */
    public $sourceEmail;
    
    /**
     * Merchant/Individual day phone number. *For USA, CAN, NZL and AUS value must be 10 characters
     *
     * @var string
     */
    public $dayPhone;
    
    /**
     * Merchant/Individual evening phone number. *For USA, CAN, NZL and AUS value must be 10 characters
     *
     * @var string
     */
    public $eveningPhone;
    
    /**
     * Communication Email Address. *ProPay’s system will send automated emails to the email address on file rather
        than the Source Email.
     *
     * @var string
     */
    public $notificationEmail;
    
    /**
     * Required to specify the currency in which funds should be held, if other than USD. An affiliation must be granted
        permission to create accounts in currencies other than USD. ISO 4217 standard 3 character currency code.
     *
     * @var string
     */
    public $currencyCode;
    
    /**
     * One of the previously assigned merchant tiers. *If not provided, will default to cheapest available tier.
     *
     * @var string
     */
    public $tier;
    
    /**
     * This is a partner’s own unique identifier. Typically used as the distributor or consultant ID.
     *
     * @var string
     */
    public $externalId;
    
    /**
     * Numeric value which will give a user access to ProPay’s IVR system. Can also be used to reset password.
     *
     * @var string
     */
    public $phonePin;
    
    /**
     * ProPay account username. Must be unique in ProPay system. *Username defaults to <sourceEmail> if userId is not
provided.
     *
     * @var string
     */
    public $userId;
    
    /**
     * Business Physical Address
     *
     * @var GlobalPayments\Api\Entities\Address
     */
    public $userAddress;
    
    /**
     * Business Physical Address
     *
     * @var GlobalPayments\Api\Entities\Address
     */
    public $mailingAddress;
    
    
    public function __construct()
    {
        $this->userAddress = new Address();
        $this->mailingAddress = new Address();
    }
}
