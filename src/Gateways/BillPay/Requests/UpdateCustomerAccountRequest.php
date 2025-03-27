<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck, RecurringPaymentMethod};
use GlobalPayments\Api\Utils\{Element, ElementTree};

class UpdateCustomerAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, RecurringPaymentMethod $recurringPaymentMethod)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:UpdateCustomerAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:UpdateCustomerAccountRequest");

        $this->buildCredentials($requestElement, $credentials);

        $bankName = "";
        $expMonth = 0;
        $expYear = 0;

        if ($recurringPaymentMethod->paymentMethod instanceof ECheck) {
            $check = $recurringPaymentMethod->paymentMethod;
            $this->et->subElement($requestElement, "bdms:ACHAccountType", $this->getDepositType($check->checkType));
            $this->et->subElement($requestElement, "bdms:ACHDepositType", $this->getACHAccountType($check->accountType));
            $bankName = $check->bankName;
        }

        /** @var Element */
        $accountHolderElement = $this->et->subElement($requestElement, "bdms:AccountHolderData");
        $addressEntityData = null;
        if ($recurringPaymentMethod->address !== null) {
            $addressEntityData = $recurringPaymentMethod->address;
        }
        $this->buildAccountHolderData($accountHolderElement, $addressEntityData, $recurringPaymentMethod->nameOnAccount);

        if ($recurringPaymentMethod->paymentMethod instanceof CreditCardData) {
            $credit = $recurringPaymentMethod->paymentMethod;
            $expMonth = $credit->expMonth;
            $expYear = $credit->expYear;
            $bankName = $credit->bankName;
        }

        if ($this->isNullOrEmpty($bankName)) {
            // Need to explicity set the empty value
            $this->et->subElement($requestElement, "bdms:BankName");
        } else {
            $this->et->subElement($requestElement, "bdms:BankName", $bankName);
        }

        if ($expMonth > 0) {
            $this->et->subElement($requestElement, "bdms:ExpirationMonth", $expMonth);
        }

        if ($expYear > 0) {
            $this->et->subElement($requestElement, "bdms:ExpirationYear", $expYear);
        }

        $this->et->subElement(
            $requestElement, 
            "bdms:IsCustomerDefaultAccount", 
            $this->serializeBooleanValues($recurringPaymentMethod->preferredPayment)
        );
        $this->et->subElement($requestElement, "bdms:MerchantCustomerID", $recurringPaymentMethod->customerKey);
        $this->et->subElement($requestElement, "bdms:NewCustomerAccountName", $recurringPaymentMethod->id);
        $this->et->subElement($requestElement, "bdms:OldCustomerAccountName", $recurringPaymentMethod->id);
        $this->et->subElement(
            $requestElement, 
            "bdms:PaymentMethod",
            $this->getPaymentMethodType($recurringPaymentMethod->paymentMethod->getPaymentMethodType())
        );

        return $this->et->toString($envelope);
    }
}