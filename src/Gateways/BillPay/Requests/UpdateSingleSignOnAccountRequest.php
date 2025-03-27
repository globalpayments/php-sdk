<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class UpdateSingleSignOnAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, Customer $customer)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:UpdateSingleSignOnAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $customerElement = $this->et->subElement($requestElement, "bdms:Customer");
        $this->buildCustomer($customerElement, $customer);

        $this->et->subElement($requestElement, "bdms:MerchantCustomerIDToUpdate", $customer->id);

        return $this->et->toString($envelope);
    }
}