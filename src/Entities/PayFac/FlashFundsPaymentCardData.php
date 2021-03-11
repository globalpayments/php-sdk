<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;

class FlashFundsPaymentCardData
{
    public $creditCard;
    public $cardholderAddress;
    
    public function __construct()
    {
        $this->creditCard = new CreditCardData();
        $this->cardholderAddress = new Address();
    }
}
