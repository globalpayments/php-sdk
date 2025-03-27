<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Utils\ElementTree;

class MakeBlindPaymentReturnTokenRequest extends MakeBlindPaymentRequest 
{
    public function __construct(ElementTree $et) 
    {
        parent::__construct($et);
    }
    
    protected function getMethodElementTagName(): string 
    {
        return "bil:MakeBlindPaymentReturnToken";
    }

    protected function getRequestElementTagName() 
    {
        return "bil:MakePaymentReturnTokenRequest";
    }
}