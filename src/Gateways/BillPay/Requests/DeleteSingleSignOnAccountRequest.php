<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class DeleteSingleSignOnAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, Customer $customer)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:DeleteSingleSignOnAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");

        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:MerchantCustomerID", $customer->id);

        return $this->et->toString($envelope);
    }
}