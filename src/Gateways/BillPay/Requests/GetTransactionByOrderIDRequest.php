<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\{ReportBuilder, TransactionReportBuilder};
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class GetTransactionByOrderIDRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et)
    {
        parent::__construct($et);
    }

    public function build(Element $envelope, ReportBuilder $builder, Credentials $credentials): string
    {
        if ($builder instanceof TransactionReportBuilder) {
            /** @var Element */
            $body = $this->et->subElement($envelope, "soapenv:Body");
            /** @var Element */
            $methodElement = $this->et->subElement($body, "bil:GetTransactionByOrderID");
            /** @var Element */
            $requestElement = $this->et->subElement($methodElement, "bil:GetTransactionByOrderIDRequest");

            $this->buildCredentials(
                $requestElement,
                $credentials
            );

            $this->et->subElement(
                $requestElement,
                'bdms:OrderID',
                $builder->transactionId
            );

            return $this->et->toString($envelope);
        } else {
            throw new BuilderException('This method only support TransactionReportBuilder.');
        }
    }
}