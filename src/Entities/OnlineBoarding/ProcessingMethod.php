<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class ProcessingMethod extends FormElement
{
    /**
     * @var string
     */
    public $processingMethodCardSwiped;
    
    /**
     * @var string
     */
    public $processingMethodKeyedWithImprint;
    
    /**
     * @var string
     */
    public $processingMethodKeyedWithoutImprint;
    
    /**
     * @var string
     */
    public $processingMethodMotoDomesticTransactions;
    
    /**
     * @var string
     */
    public $processingMethodMotoForeignTransactions;
    
    /**
     * @var string
     */
    public $processingMethodPercentOfGiftCardSales;
}
