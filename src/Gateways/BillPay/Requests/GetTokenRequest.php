<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class GetTokenRequest extends BillPayRequestBase
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
        $methodElement = $this->et->subElement($body, "bil:GetToken");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:GetTokenRequest");
        /** @var CreditCardData */
        $card = $builder->paymentMethod;

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $accountHolderDataElement = $this->et->subElement($requestElement, "bdms:AccountHolderData");
        if ($builder->billingAddress !== null) {
            $this->et->subElement(
                $accountHolderDataElement, 
                "pos:Zip",
                $builder->billingAddress->postalCode
            );
        }
        $this->et->subElement($requestElement, "bdms:AccountNumber", $card->number);
        // PLACEHOLDER ClearTrackData
        // PLACEHOLDER E3KTB
        // PLACEHOLDER e3TrackData
        // PLACEHOLDER e3TrackType
        $this->et->subElement($requestElement, "bdms:ExpirationMonth", $card->expMonth);
        $this->et->subElement($requestElement, "bdms:ExpirationYear", $card->expYear);
        $this->et->subElement(
            $requestElement, 
            "bdms:PaymentMethod", 
            $this->getPaymentMethodType($card->paymentMethodType)
        );

        return $this->et->toString($envelope);
    }
}