<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\OwnerOfficerSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class OwnerOfficer extends FormElement
{
    /**
     * @var string
     */
    public $firstName;
    
    /**
     * @var string
     */
    public $lastName;
    
    /**
     * @var string
     */
    public $fullName;
    
    /**
     * @var string
     */
    public $title;
    
    /**
     * @var DateTime
     */
    public $dateOfBirth;
    
    /**
     * @var OwnerOfficerSelect
     */
    public $ownershipTypeSelect;
    
    /**
     * @var string
     */
    public $equityOwnership;
    
    /**
     * @var States
     */
    public $emailAddress;
    
    /**
     * @var string
     */
    public $lengthAtHomeAddress;
    
    /**
     * @var string
     */
    public $driversLicenseNumber;
    
    /**
     * @var string
     */
    public $homePhone;
    
    /**
     * @var string
     */
    public $cellPhone;
    
    /**
     * @var string
     */
    public $sSN;
    
    /**
     * @var string
     */
    public $street;
    
    /**
     * @var string
     */
    public $city;
    
    /**
     * @var States
     */
    public $stateSelect;
    
    /**
     * @var string
     */
    public $zip;
}
