<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class UpdateTokenRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et)
    {
        parent::__construct($et);
    }

    public function build(Element $envelope, CreditCardData $card, Credentials $credentials)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:UpdateTokenExpirationDate");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:UpdateTokenExpirationDateRequest");

        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:ExpirationMonth", $card->expMonth);
        $this->et->subElement($requestElement, "bdms:ExpirationYear", $card->expYear);
        $this->et->subElement($requestElement, "bdms:Token", $card->token);

        return $this->et->toString($envelope);
    }
}