<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\PaymentMethods\{
    CreditCardData,
    ECheck,
    RecurringPaymentMethod
};
use GlobalPayments\Api\Utils\{Element, ElementTree};

class CreateCustomerAccountRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, RecurringPaymentMethod $recurringPaymentMethod)
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:SaveCustomerAccount");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:SaveCustomerAccountRequest");

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $customerAccountElement = $this->et->subElement($requestElement, "bdms:CustomerAccount");

        $accountNumber = "";
        $routingNumber = "";
        $bankName = "";
        $expMonth = 0;
        $expYear = 0;

        if ($recurringPaymentMethod->paymentMethod instanceof ECheck) {
            $check = $recurringPaymentMethod->paymentMethod;
            $this->et->subElement($customerAccountElement, "bdms:ACHAccountType", $this->getDepositType($check->checkType));
            $this->et->subElement($customerAccountElement, "bdms:ACHDepositType", $this->getACHAccountType($check->accountType));
            $accountNumber = $check->accountNumber;
            $routingNumber = $check->routingNumber;
            $bankName = $check->bankName;
        }

        /** @var Element */
        $accountHolder = $this->et->subElement($customerAccountElement, "bdms:AccountHolderData");
        $addressEntityData = null;
        if ($recurringPaymentMethod->address !== null) {
            $addressEntityData = $recurringPaymentMethod->address;
        }
        $this->buildAccountHolderData($accountHolder, $addressEntityData, $recurringPaymentMethod->nameOnAccount);

        if ($recurringPaymentMethod->paymentMethod instanceof CreditCardData) {
            $credit = $recurringPaymentMethod->paymentMethod;
            $accountNumber = $credit->number;
            $expMonth = $credit->expMonth;
            $expYear = $credit->expYear;
            $bankName = $credit->bankName;
        }

        $this->et->subElement($customerAccountElement, "bdms:AccountNumber", $accountNumber);

        if ($this->isNullOrEmpty($bankName)) {
            $this->et->subElement($customerAccountElement, "bdms:BankName");
        } else {
            $this->et->subElement($customerAccountElement, "bdms:BankName", $bankName);
        }

        $this->et->subElement($customerAccountElement, "bdms:CustomerAccountName", $recurringPaymentMethod->id);

        if ($expMonth > 0) {
            $this->et->subElement($customerAccountElement, "bdms:ExpirationMonth", $expMonth);
        }

        if ($expYear > 0) {
            $this->et->subElement($customerAccountElement, "bdms:ExpirationYear", $expYear);
        }

        $this->et->subElement(
            $customerAccountElement, 
            "bdms:IsCustomerDefaultAccount", 
            $this->serializeBooleanValues($recurringPaymentMethod->preferredPayment)
        );
        $this->et->subElement($customerAccountElement, "bdms:RoutingNumber", $routingNumber);

        if ($recurringPaymentMethod->paymentMethod !== null) {
            $this->et->subElement(
                $customerAccountElement, 
                "bdms:TokenPaymentMethod",
                $this->getPaymentMethodType($recurringPaymentMethod->paymentMethod->getPaymentMethodType())
            );
        }

        $this->et->subElement($requestElement, "bdms:MerchantCustomerID", $recurringPaymentMethod->customerKey);

        return $this->et->toString($envelope);
    }
}