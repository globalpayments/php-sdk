<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class DepositOptions extends FormElement
{

    /**
     * @var string
     */
    public $depositMethodTypeSelect;
    
    /**
     * @var string
     */
    public $settlementOptionSelect;
}
