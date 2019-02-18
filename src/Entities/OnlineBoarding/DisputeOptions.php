<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\DBALegal;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\EmailFax;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class DisputeOptions extends FormElement
{
    /**
     * @var DBALegal
     */
    public $mailingOptionSelect;
    
    /**
     * @var EmailFax
     */
    public $electronicOptionSelect;
}
