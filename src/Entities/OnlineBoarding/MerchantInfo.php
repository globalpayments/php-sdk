<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\PhoneTypeSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class MerchantInfo extends FormElement
{
    /**
     * @var string
     */
    public $affiliatePartnerId;
    
    /**
     * @var string
     */
    public $federalTaxId;
    
    /**
     * @var string
     */
    public $relationshipManagerName;
    
    /**
     * @var string
     */
    public $relationshipManagerPhone;
    
    /**
     * @var string
     */
    public $merchantDbaName;
    
    /**
     * @var string
     */
    public $merchantStreet;
    
    /**
     * @var string
     */
    public $merchantCity;
    
    /**
     * @var States
     */
    public $merchantStatesSelect;
    
    /**
     * @var string
     */
    public $merchantZip;
    
    /**
     * @var string
     */
    public $merchantEmail;
    
    /**
     * @var string
     */
    public $merchantEmailFirstName;
    
    /**
     * @var string
     */
    public $merchantEmailLastName;
    
    /**
     * @var string
     */
    public $merchantPrimaryContactName;
    
    /**
     * @var string
     */
    public $merchantPrimaryContactPhone;
    
    /**
     * @var string
     */
    public $primaryContactPhoneTypeSelect;
    
    /**
     * @var string
     */
    public $merchantSecondaryContactName;
    
    /**
     * @var string
     */
    public $merchantSecondaryContactPhone;
    
    /**
     * @var PhoneTypeSelect
     */
    public $secondaryContactPhoneTypeSelect;
    
    /**
     * @var int
     */
    public $merchantNumberOfLocations;
    
    /**
     * @var string
     */
    public $merchantStoreNumber;
    
    /**
     * @var string
     */
    public $merchantPhone;
    
    /**
     * @var string
     */
    public $merchantFax;
    
    /**
     * @var string
     */
    public $merchantCustomerServiceNumber;
    
    /**
     * @var string
     */
    public $merchantWebsiteAddress;
}
