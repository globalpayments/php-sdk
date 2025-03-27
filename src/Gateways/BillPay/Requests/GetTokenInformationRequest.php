<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class GetTokenInformationRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) 
    {
        parent::__construct($et);
    }

    public function build(Element $envelope, AuthorizationBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:GetTokenInformation");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");   
        
        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:Token", $builder->paymentMethod->token);

        return $this->et->toString($envelope);
    }
}