<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class ShippingOptions extends FormElement
{
    /**
     * @var string
     */
    public $gatewayAddPrinter;
    
    /**
     * @var string
     */
    public $equipmentGrandTotalDueToHps;
}
