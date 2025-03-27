<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class CreateSingleSignOnAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    /**
     * 
     * @param Element $envelope
     * @param Credentials $credentials 
     * @param Customer $customer 
     * @return string|null 
     * @throws ApiException 
     */
    public function build(Element $envelope, Credentials $credentials, Customer $customer)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:CreateSingleSignOnAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $customerElement = $this->et->subElement($requestElement, "bdms:Customer");

        $this->buildCustomer($customerElement, $customer);
        return $this->et->toString($envelope);
    }
}