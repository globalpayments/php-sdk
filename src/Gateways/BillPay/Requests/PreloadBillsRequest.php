<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class PreloadBillsRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) {
        parent::__construct($et);
    }

    public function build(Element $envelope, BillingBuilder $builder, Credentials $credentials): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:PreloadBills");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:PreloadBillsRequest");

        $this->validateBills($builder->getBills());
        $this->buildCredentials($requestElement, $credentials);
        /** @var Element */
        $bills = $this->et->subElement($requestElement, "bdms:Bills");

        foreach($builder->getBills() as $bill) { 
            /** @var Element */
            $billElement = $this->et->subElement($bills, "bdms:Bill");
            /** @var Element */
            $billIdentifierExtended = $this->et->subElement($billElement, "bdms:BillIdentifierExtended");

            $this->et->subElement($billIdentifierExtended, "bdms:BillType", $bill->getBillType());
            $this->et->subElement($billIdentifierExtended, "bdms:ID1", $bill->getIdentifier1());
            $this->et->subElement($billIdentifierExtended, "bdms:ID2", $bill->getIdentifier2());
            $this->et->subElement($billIdentifierExtended, "bdms:ID3", $bill->getIdentifier3());
            $this->et->subElement($billIdentifierExtended, "bdms:ID4", $bill->getIdentifier4());

            $this->et->subElement(
                $billIdentifierExtended, 
                "bdms:DueDate", 
                $this->getDateFormatted($bill->getDueDate())
            );

            $this->et->subElement(
                $billElement,
                "bdms:BillPresentment",
                $this->getBillPresentmentType($bill->getBillPresentment())
            );

            if ($bill->getCustomer() !== null) {
                /** @var Customer */
                $customer = $bill->getCustomer();

                if ($customer->address !== null) {
                    $address = $customer->address;
                    /** @var Element */
                    $customerAddress = $this->et->subElement($billElement, "bdms:CustomerAddress");
                    $this->et->subElement($customerAddress, "bdms:AddressLineOne", $address->streetAddress1);
                    $this->et->subElement($customerAddress, "bdms:City", $address->city);
                    $this->et->subElement($customerAddress, "bdms:Country", $address->country);
                    $this->et->subElement($customerAddress, "bdms:PostalCode", $address->postalCode);
                    $this->et->subElement($customerAddress, "bdms:State", $address->state);
                }

                $this->et->subElement($billElement, "bdms:MerchantCustomerId", $customer->id);
                $this->et->subElement($billElement, "bdms:ObligorEmailAddress", $customer->email);
                $this->et->subElement($billElement, "bdms:ObligorFirstName", $customer->firstName);
                $this->et->subElement($billElement, "bdms:ObligorLastName", $customer->lastName);
                $this->et->subElement($billElement, "bdms:ObligorPhoneNumber", $customer->homePhone);
            }

            $this->et->subElement($billElement, "bdms:RequiredAmount", $bill->getAmount());
        }

        return $this->et->toString($envelope);
    }
}