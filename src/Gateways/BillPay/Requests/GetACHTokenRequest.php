<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class GetACHTokenRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
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
        /** @var ECheck */
        $ach = $builder->paymentMethod;

        $this->buildCredentials($requestElement, $credentials);

        $this->et->subElement($requestElement, "bdms:ACHAccountType", $this->getDepositType($ach->checkType));
        $this->et->subElement($requestElement, "bdms:ACHDepositType", $this->getACHAccountType($ach->accountType));
        $this->et->subElement($requestElement, "bdms:ACHStandardEntryClass", $ach->secCode);

        /** @var Element */
        $accountHolderDataElement = $this->et->subElement($requestElement, "bdms:AccountHolderData");
        if (!$this->isNullOrEmpty($ach->checkHolderName)) {
            $parts = explode(" ", $ach->checkHolderName);
            $this->et->subElement($accountHolderDataElement, "pos:LastName", end($parts));
        }

        $this->et->subElement($requestElement, "bdms:AccountNumber", $ach->accountNumber);
        $this->et->subElement($requestElement, "bdms:PaymentMethod", $this->getPaymentMethodType($ach->paymentMethodType));
        $this->et->subElement($requestElement, "bdms:RoutingNumber", $ach->routingNumber);

        return $this->et->toString($envelope);
    }
}