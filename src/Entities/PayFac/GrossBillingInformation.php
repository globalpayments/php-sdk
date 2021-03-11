<?php
namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GrossBillingInformation
{

    public $grossSettleAddress;

    public $grossSettleBankData;
    
    public $grossSettleCreditCardData;

    public function __construct()
    {
        $this->grossSettleAddress = new Address();
        $this->grossSettleBankData = new BankAccountData();
        $this->grossSettleCreditCardData = new CreditCardData();
    }
}
