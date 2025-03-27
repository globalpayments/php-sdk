<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class ReversePaymentRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    /**
     * 
     * @param Element $envelope
     * @param ManagementBuilder $builder
     * @param Credentials $credentials 
     * 
     * @return string|null
     * @throws ApiException
     */
    public function build(Element $envelope, ManagementBuilder $builder, Credentials $credentials)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:ReversePayment");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:ReversePaymentRequest");

        $this->validateReversal($builder);
        $this->buildCredentials($requestElement, $credentials);

        $amount = (float) $builder->amount;
        if ($amount !== null && $amount > 0.0) {
            $this->et->subElement(
                $requestElement,
                "bdms:BaseAmountToRefund",
                $amount
            );
        }

        /** @var Element */
        $billsToReverse = $this->et->subElement(
            $requestElement,
            "bdms:BillsToReverse"
        );

        if ($builder->bills !== null && count($builder->bills) > 0) {
            $this->buildBillTransactions(
                $billsToReverse,
                $builder->bills,
                "bdms:ReversalBillTransaction",
                "bdms:AmountToReverse"
            );
        }

        $this->et->subElement($requestElement, "bdms:EndUserBrowserType", $this->browserType);
        $this->et->subElement($requestElement, "bdms:EndUserIPAddress", $builder->customerIpAddress);
        $this->et->subElement($requestElement, "bdms:ExpectedFeeAmountToRefund", $builder->convenienceAmount);
        $this->et->subElement($requestElement, "bdms:OrderIDOfReversal", $builder->orderId);
        // PLACEHOLDER ReversalReason
        /** @var TransactionReference*/
        $transactionRef = $builder->paymentMethod;
        $this->et->subElement(
            $requestElement, 
            "bdms:Transaction_ID",
            $transactionRef->transactionId
        );

        return $this->et->toString($envelope);
    }
}