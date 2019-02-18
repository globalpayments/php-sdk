<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class LegalInfo extends FormElement
{
    /**
     * @var string
     */
    public $corporateName;
    
    /**
     * @var string
     */
    public $corporateStreet;
    
    /**
     * @var string
     */
    public $corporateCity;
    
    /**
     * @var States
     */
    public $corporateStatesSelect;
    
    /**
     * @var string
     */
    public $corporateZip;
    
    /**
     * @var string
     */
    public $corporatePhone;
    
    /**
     * @var string
     */
    public $corporateFax;
}
