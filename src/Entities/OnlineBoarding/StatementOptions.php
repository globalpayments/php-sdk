<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\DBALegalElectronic;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class StatementOptions extends FormElement
{
    /**
     * @var DBALegalElectronic
     */
    public $statementMailDestinationOptionSelect;
    
    /**
     * @var string
     */
    public $statementInfoCentralOrPreferredEmail;
    
    /**
     * @var string
     */
    public $statementPreferredEmailAddress;
}
