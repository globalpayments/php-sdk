<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Utils\ElementTree;

class MakeQuickPayBlindPaymentReturnTokenRequest extends MakeQuickPayBlindPaymentRequest
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    protected function getMethodElementTagName(): string
    {
        return "bil:MakeQuickPayBlindPaymentReturnToken";
    }
}