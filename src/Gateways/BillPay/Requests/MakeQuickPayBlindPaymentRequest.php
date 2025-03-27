<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck};
use GlobalPayments\Api\PaymentMethods\Interfaces\{ICardData, ITokenizable};
use GlobalPayments\Api\Utils\{Element, ElementTree};

class MakeQuickPayBlindPaymentRequest extends BillPayRequestBase
{
    protected static $SOAPENV_BODY = "soapenv:Body";

    protected static $BDMS_BILLTRANSACTIONS = "bdms:BillTransactions";

    protected static $BDMS_BILLTRANSACTION = "bdms:BillTransaction";

    protected static $BDMS_AMOUNTTOAPPLYTOBILL = "bdms:AmountToApplyToBill";

    protected static $BDMS_ENDUSERBROWSERTYPE = "bdms:EndUserBrowserType";

    protected static $BDMS_ENDUSERIPADDRESS = "bdms:EndUserIPAddress";

    protected static $BDMS_ORDERID = "bdms:OrderID";

    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    protected function getMethodElementTagName(): string
    {
        return "bil:MakeQuickPayBlindPayment";
    }

    protected function getRequestElementTagName(): string
    {
        return "bil:request";
    }

    public function build(Element $envelope, AuthorizationBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, MakeQuickPayBlindPaymentRequest::$SOAPENV_BODY);
        /** @var Element */
        $methodElement = $this->et->subElement($body, $this->getMethodElementTagName());
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, $this->getRequestElementTagName());

        $hasToken = $this->hasToken($builder);
        $hasCardData = $this->hasCardData($builder);
        $hasACHData = $this->hasACHData($builder);

        $amount = $builder->amount !== null ? $builder->amount : 0.0;

        if (!$hasToken && !$hasCardData && !$hasACHData) {
            throw new UnsupportedTransactionException("Payment method not accepted");
        }

        $this->validateTransaction($builder);

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $billTransactions = $this->et->subElement($requestElement, MakeQuickPayBlindPaymentRequest::$BDMS_BILLTRANSACTIONS);
        $this->buildBillTransactions(
            $billTransactions,
            $builder->bills,
            MakeQuickPayBlindPaymentRequest::$BDMS_BILLTRANSACTION, 
            MakeQuickPayBlindPaymentRequest::$BDMS_AMOUNTTOAPPLYTOBILL
        );

        // QuickPayACHAccountToCharge
        if ($builder->paymentMethod instanceof ECheck) {
            $check = $builder->paymentMethod;

            if (!$this->isNullOrEmpty($check->token)) {
                $this->buildQuickPayACHAccountToCharge(
                    $requestElement,
                    $check, 
                    $builder->amount, 
                    $builder->convenienceAmount
                );
            } else {
                throw new UnsupportedTransactionException("Quick Pay token must be provided for this transaction");
            }
        }

        // QuickPayToCharge
        if ($builder->paymentMethod instanceof CreditCardData) {
            $credit = $builder->paymentMethod;

            if (!$this->isNullOrEmpty($credit->token)) {
                $this->buildQuickPayCardToCharge(
                    $requestElement,
                    $credit, 
                    $amount, 
                    $builder->billingAddress, 
                    $builder->convenienceAmount
                );
            } else {
                throw new UnsupportedTransactionException("Quick Pay token must be provided for this transaction");
            }
        }

        $this->et->subElement($requestElement, MakeQuickPayBlindPaymentRequest::$BDMS_ENDUSERBROWSERTYPE, $this->browserType);
        $this->et->subElement($requestElement, MakeQuickPayBlindPaymentRequest::$BDMS_ENDUSERIPADDRESS, $builder->customerIpAddress);
        $this->et->subElement($requestElement, MakeQuickPayBlindPaymentRequest::$BDMS_ORDERID, $builder->orderId);

        $this->buildTransaction($requestElement, $builder);

        return $this->et->toString($envelope);
    }

    private function hasToken(AuthorizationBuilder $builder): bool
    {
        return ($builder->paymentMethod instanceof ITokenizable && isset($builder->paymentMethod->token) && $builder->paymentMethod->token !== '');
    }

    private function hasCardData(AuthorizationBuilder $builder): bool
    {
        return ($builder->paymentMethod instanceof ICardData && isset($builder->paymentMethod->number) && $builder->paymentMethod->number !== '');
    }

    private function hasACHData(AuthorizationBuilder $builder): bool
    {
        return ($builder->paymentMethod instanceof ECheck && isset($builder->paymentMethod->accountNumber) && $builder->paymentMethod->accountNumber !== '');
    }
}