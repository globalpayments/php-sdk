<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\AMPM;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\YesNo;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\YesNoDataSecurity;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class EquipmentInfo extends FormElement
{
    /**
     * @var string
     */
    public $infoCentralEmailAddress;
    
    /**
     * @var string
     */
    public $gatewayIndustrySelect;
    
    /**
     * @var string
     */
    public $equipmentIndustrySelect;
    
    /**
     * @var YesNo
     */
    public $gatewayDeviceTypeSelect;
    
    /**
     * @var string
     */
    public $gatewayVersionSelect;
    
    /**
     * @var string
     */
    public $gatewayTimeZoneSelect;
    
    /**
     * @var YesNo
     */
    public $gatewayAutoCloseSelect;
    
    /**
     * @var YesNoDataSecurity
     */
    public $gatewayAutoCloseTimeHour;
    
    /**
     * @var AMPM
     */
    public $gatewayAutoCloseAmPmSelect;
    
    /**
     * @var string
     */
    public $developerName;
    
    /**
     * @var string
     */
    public $developerEmail;
    
    /**
     * @var string
     */
    public $fraudScreeningOptOut;
    
    /**
     * @var string
     */
    public $paypalThroughHeartlandOptOut;
    
    /**
     * @var string
     */
    public $masterpassThroughHeartlandOptOut;
    
    /**
     * @var string
     */
    public $avsDeclineAll;
    
    /**
     * @var string
     */
    public $merchantEquipmentQuantity1;
}
