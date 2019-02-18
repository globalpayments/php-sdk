<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class BankingInfo extends FormElement
{
    /**
     * @var string
     */
    public $fuelSupplierCompany;
    
    /**
     * @var string
     */
    public $bankName;
    
    /**
     * @var string
     */
    public $bankPhone;
    
    /**
     * @var string
     */
    public $bankStreet;
    
    /**
     * @var string
     */
    public $bankCity;
    
    /**
     * @var string
     */
    public $bankStatesSelect;
    
    /**
     * @var string
     */
    public $bankZip;
    
    /**
     * @var array(BankAccount)
     */
    public $bankAccounts = [];
}
