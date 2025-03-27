<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Utils\ElementTree;

class MakePaymentRequest extends MakeBlindPaymentRequest
{
    public function __construct(ElementTree $et) 
    {
        parent::__construct($et);
    }

    protected function getMethodElementTagName(): string 
    {
        return "bil:MakePayment";
    }

    protected function getRequestElementTagName() 
    {
        return "bil:MakeE3PaymentRequest";
    }
}