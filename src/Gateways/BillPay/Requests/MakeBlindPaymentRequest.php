<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck};
use GlobalPayments\Api\PaymentMethods\Interfaces\{ICardData, ITokenizable};
use GlobalPayments\Api\Utils\{Element, ElementTree};

class MakeBlindPaymentRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) 
    {
        parent::__construct($et);
    }

    protected function getMethodElementTagName(): string 
    {
        return "bil:MakeBlindPayment";
    }

    protected function getRequestElementTagName() 
    {
        return "bil:MakeE3PaymentRequest";
    }

    public function build(Element $envelope, AuthorizationBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, $this->getMethodElementTagName());
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, $this->getRequestElementTagName());

        $hasToken = ($builder->paymentMethod instanceof ITokenizable && isset($builder->paymentMethod->token) && $builder->paymentMethod->token !== '');
        // Would EntryMethod.Manual be clear Swipe?
        $hasCardData = ($builder->paymentMethod instanceof ICardData && isset($builder->paymentMethod->number) && $builder->paymentMethod->number !== '');
        $hasACHData = ($builder->paymentMethod instanceof ECheck && isset($builder->paymentMethod->accountNumber) && $builder->paymentMethod->accountNumber !== '');

        $amount = isset($builder->amount) ? $builder->amount : 0.0;

        // Only allow token, card, and ACH data instances
        if (!$hasToken && !$hasCardData && !$hasACHData) {
            throw new UnsupportedTransactionException("Payment method not accepted");
        }

        $this->validateTransaction($builder);

        $this->buildCredentials($requestElement, $credentials);

        if (!$hasToken && $builder->paymentMethod instanceof ECheck) {
            $eCheck = $builder->paymentMethod;
            $this->buildACHAccount($requestElement, $eCheck, $amount, $builder->convenienceAmount);
        }

        /** @var Element */
        $billTransactions = $this->et->subElement($requestElement, "bdms:BillTransactions");
        $this->buildBillTransactions(
            $billTransactions,
            $builder->bills,
            'bdms:BillTransaction',
            'bdms:AmountToApplyToBill'
        );
        // PLACEHOLDER: ClearSwipe

        // ClearTextCredit
        if ($hasCardData && $builder->paymentMethod instanceof CreditCardData) {
            $creditCardData = $builder->paymentMethod;
            $this->buildClearTextCredit(
                $requestElement,
                $creditCardData,
                $amount,
                $builder->convenienceAmount,
                $builder->emvFallbackCondition,
                $builder->emvLastChipRead,
                $builder->billingAddress
            );
        }

        // PLACEHOLDER: E3Credit
        // PLACEHOLDER: E3DebitWithPIN
        $this->et->subElement($requestElement, "bdms:EndUserBrowserType", $this->browserType);
        $this->et->subElement($requestElement, "bdms:EndUserIPAddress", $builder->customerIpAddress);
        $this->et->subElement($requestElement, "bdms:OrderID", $builder->orderId);
        // PLACEHOLDER: PAXDevices
        // PLACEHOLDER: TimeoutInSeconds
        if ($hasToken) {
            $this->buildTokenToCharge(
                $requestElement,
                $builder->paymentMethod,
                $amount,
                $builder->convenienceAmount
            );
        }

        $this->buildTransaction($requestElement, $builder);

        return $this->et->toString($envelope);
    }
}
