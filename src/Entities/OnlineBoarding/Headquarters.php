<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class Headquarters extends FormElement
{
    /**
     * @var string
     */
    public $headquartersName;
    
    /**
     * @var string
     */
    public $headquartersStreet;
    
    /**
     * @var string
     */
    public $headquartersCity;
    
    /**
     * @var States
     */
    public $headquartersStatesSelect;
    
    /**
     * @var string
     */
    public $headquartersZip;
    
    /**
     * @var string
     */
    public $headquartersPhone;
    
    /**
     * @var string
     */
    public $headquartersFax;
}
