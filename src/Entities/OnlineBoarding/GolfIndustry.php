<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class GolfIndustry extends FormElement
{
    /**
     * @var string
     */
    public $golfPercentPublic;
    
    /**
     * @var string
     */
    public $golfPercentPrivate;
    
    /**
     * @var string
     */
    public $golfPercentMembership;
    
    /**
     * @var string
     */
    public $golfPercentProShop;
    
    /**
     * @var string
     */
    public $golfPercentRestaurantOther;
}
