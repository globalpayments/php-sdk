<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\AllCardsAcceptedConsume;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\AutomatedFuelAFDConve;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\ByBatchByCardTypeStand;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class UnknownFields extends FormElement
{
    /**
     * @var ByBatchByCardTypeStand
     */
    public $statementOptionSelect;
    
    /**
     * @var AutomatedFuelAFDConve
     */
    public $interchangeQualificationTypeSelect;
    
    /**
     * @var AllCardsAcceptedConsume
     */
    public $cardAcceptanceTypeSelect;
    
    /**
     * @var string
     */
    public $recurringMinimumDiscountFee;
    
    /**
     * @var string
     */
    public $recurringServiceRegulatoryFee;
    
    /**
     * @var string
     */
    public $recurringAnnualFee;
    
    /**
     * @var string
     */
    public $recurringChargebackFee;
    
    /**
     * @var string
     */
    public $reccuringVoiceAuthFee;
}
