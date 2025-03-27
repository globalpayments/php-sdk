<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class CommitPreloadedBillsRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:CommitPreloadedBills");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:CommitPreloadedBillsRequest");

        $this->buildCredentials($requestElement, $credentials);

        return $this->et->toString($envelope);
    }
}