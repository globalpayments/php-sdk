<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class TransactionInfo extends FormElement
{
    /**
     * @var string
     */
    public $annualVolume;
    
    /**
     * @var string
     */
    public $averageTicket;
    
    /**
     * @var string
     */
    public $highTicket;
    
    /**
     * @var string
     */
    public $highTicketFrequency;
    
    /**
     * @var string
     */
    public $achAnnualVolume;
    
    /**
     * @var string
     */
    public $achAverageTicket;
    
    /**
     * @var string
     */
    public $amexOptOut;
    
    /**
     * @var string
     */
    public $amexMerchantNumber;
    
    /**
     * @var string
     */
    public $amexOptBlue;
    
    /**
     * @var string
     */
    public $amexAnnualVolume;
    
    /**
     * @var string
     */
    public $amexAverageTicket;
    
    /**
     * @var string
     */
    public $amexMarketingMaterialOptOut;
    
    /**
     * @var string
     */
    public $discoverOptOut;
    
    /**
     * @var string
     */
    public $paypalOptOut;
    
    /**
     * @var string
     */
    public $onePoint;
}
