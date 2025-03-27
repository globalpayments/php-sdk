<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class LoadSecurePayRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, BillingBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:LoadSecurePayDataExtended");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");
        $hostedPaymentData = $builder->getHostedPaymentData();

        $this->validateLoadSecurePay($hostedPaymentData);

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $billsElement = $this->et->subElement($requestElement, "bdms:BillData");
        
        if ($hostedPaymentData !== null) {
            $customerIsEditable = $this->serializeBooleanValues($hostedPaymentData->customerIsEditable);
    
            foreach($hostedPaymentData->bills as $bill) {
                /** @var Element */
                $billElement = $this->et->subElement($billsElement, "bdms:SecurePayBill");

                $this->et->subElement($billElement, "bdms:Amount", $bill->getAmount());
                $this->et->subElement($billElement, "bdms:BillTypeName", $bill->getBillType());
                $this->et->subElement($billElement, "bdms:Identifier1", $bill->getIdentifier1());
                $this->et->subElement($billElement, "bdms:Identifier2", $bill->getIdentifier2());
                $this->et->subElement($billElement, "bdms:Identifier3", $bill->getIdentifier3());
                $this->et->subElement($billElement, "bdms:Identifier4", $bill->getIdentifier4());
            }

            $this->et->subElement(
                $requestElement, 
                "bdms:SecurePayPaymentType_ID", 
                $this->getHostedPaymentTypeOrdinal($hostedPaymentData->hostedPaymentType)
            );
            $this->et->subElement($requestElement, "bdms:ReturnURL", $hostedPaymentData->merchantResponseUrl);
            $this->et->subElement($requestElement, "bdms:CancelURL", $hostedPaymentData->cancelUrl);

            $merchantCustomerId = null;
            if (!$this->isNullOrEmpty($hostedPaymentData->customerKey)) {
                $merchantCustomerId = $hostedPaymentData->customerKey;
            } else if (!$this->isNullOrEmpty($hostedPaymentData->customerNumber)) {
                $merchantCustomerId = $hostedPaymentData->customerNumber;
            }

            $this->et->subElement($requestElement, "bdms:MerchantCustomerID", $merchantCustomerId);
            $this->et->subElement($requestElement, "bdms:OrderID", $builder->getOrderId());
            $this->et->subElement($requestElement, "bdms:PayorEmailAddress", $hostedPaymentData->customerEmail);
            $this->et->subElement($requestElement, "bdms:PayorEmailAddressIsEditable", $customerIsEditable);
            $this->et->subElement($requestElement, "bdms:PayorFirstName", $hostedPaymentData->customerFirstName);
            $this->et->subElement($requestElement, "bdms:PayorFirstNameIsEditable", $customerIsEditable);
            $this->et->subElement($requestElement, "bdms:PayorLastName", $hostedPaymentData->customerLastName);
            $this->et->subElement($requestElement, "bdms:PayorLastNameIsEditable", $customerIsEditable);
            $this->et->subElement($requestElement, "bdms:PayorMiddleNameIsEditable", $customerIsEditable);
            $this->et->subElement($requestElement, "bdms:PayorPhoneNumber", $hostedPaymentData->customerPhoneMobile);
            $this->et->subElement($requestElement, "bdms:PayorPhoneNumberIsEditable", $customerIsEditable);

            if ($hostedPaymentData->customerAddress !== null) {
                $address = $hostedPaymentData->customerAddress;
                $this->et->subElement($requestElement, "bdms:PayorAddress", $address->streetAddress1);
                $this->et->subElement($requestElement, "bdms:PayorAddressIsEditable", $customerIsEditable);
                $this->et->subElement($requestElement, "bdms:PayorBusinessNameIsEditable", $customerIsEditable);
                $this->et->subElement($requestElement, "bdms:PayorCity", $address->city);
                $this->et->subElement($requestElement, "bdms:PayorCityIsEditable", $customerIsEditable);
                $this->et->subElement($requestElement, "bdms:PayorCountry", $address->countryCode);
                $this->et->subElement($requestElement, "bdms:PayorCountryIsEditable", $customerIsEditable);
                $this->et->subElement($requestElement, "bdms:PayorPostalCode", $address->postalCode);
                $this->et->subElement($requestElement, "bdms:PayorPostalCodeIsEditable", $customerIsEditable);
                $this->et->subElement($requestElement, "bdms:PayorState", $address->state);
                $this->et->subElement($requestElement, "bdms:PayorStateIsEditable", $customerIsEditable);
            }
        }

        return $this->et->toString($envelope);
    }
}