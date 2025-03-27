<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck};
use GlobalPayments\Api\Utils\{Element, ElementTree};

class GetConvenienceFeeRequest extends BillPayRequestBase 
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, BillingBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:GetConvenienceFee");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:GetConvenienceFeeRequest");

        $accountNumber = null;
        $routingNumber = null;
        $paymentMethod = null;

        if ($builder->paymentMethod instanceof ECheck) {
            $check = $builder->paymentMethod;
            $routingNumber = $check->routingNumber;
            $paymentMethod = "ACH";
        } else if ($builder->paymentMethod instanceof CreditCardData) {
            $credit = $builder->paymentMethod;
            $accountNumber = $credit->number;
        }

        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:BaseAmount", $builder->getAmount());

        if ($accountNumber !== null) {
            $this->et->subElement($requestElement, "bdms:CardNumber", $accountNumber);
        }

        $this->et->subElement(
            $requestElement, 
            "bdms:CardProcessingMethod",
            $this->getCardProcessingMethod($builder->paymentMethod->getPaymentMethodType())
        );

        if ($paymentMethod !== null) {
            $this->et->subElement($requestElement, "bdms:PaymentMethod", $paymentMethod);
        }

        $this->et->subElement($requestElement, "bdms:RoutingNumber", $routingNumber);

        return $this->et->toString($envelope);
    }
}