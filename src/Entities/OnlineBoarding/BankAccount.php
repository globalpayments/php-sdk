<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\BankAccountTypeSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\FundsTransferMethodSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class BankAccount extends FormElement
{
    /**
     * @var string
     */
    public $accountNumber;
    
    /**
     * @var BankAccountTypeSelect
     */
    public $accountTypeSelect;
    
    /**
     * @var string
     */
    public $transitRouterAbaNumber;
    
    /**
     * @var FundsTransferMethodSelect
     */
    public $transferMethodTypeSelect;
}
