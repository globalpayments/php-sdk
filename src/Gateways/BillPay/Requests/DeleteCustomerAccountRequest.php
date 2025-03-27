<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class DeleteCustomerAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, RecurringPaymentMethod $recurringPaymentMethod)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:DeleteCustomerAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:DeleteCustomerAccountRequest");

        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:CustomerAccountNameToDelete", $recurringPaymentMethod->id);
        $this->et->subElement($requestElement, "bdms:MerchantCustomerID", $recurringPaymentMethod->customerKey);

        return $this->et->toString($envelope);
    }
}