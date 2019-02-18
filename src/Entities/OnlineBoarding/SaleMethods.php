<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class SaleMethods extends FormElement
{
    /**
     * @var string
     */
    public $salesMethodOnPremiseFaceToFaceSales;
    
    /**
     * @var string
     */
    public $salesMethodOffPremiseFaceToFaceSales;
    
    /**
     * @var string
     */
    public $salesMethodMailOrderSales;
    
    /**
     * @var string
     */
    public $salesMethodRealTimeInternetSales;
    
    /**
     * @var string
     */
    public $salesMethodInboundTelephoneSales;
    
    /**
     * @var string
     */
    public $salesMethodOutboundTelephoneSales;
    
    /**
     * @var string
     */
    public $salesMethodInternetKeyed;
    
    /**
     * @var string
     */
    public $salesMethodRecurringBilling;
}
