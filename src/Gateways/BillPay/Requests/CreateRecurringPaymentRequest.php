<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use DateTime;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\{Customer, Schedule};
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Utils\{Element, ElementTree};

class CreateRecurringPaymentRequest extends BillPayRequestBase
{
    public function __construct(ElementTree $et) 
    {   
        parent::__construct($et);
    }

    public function build(Element $envelope, Credentials $credentials, Schedule $schedule): string
    {
        /** @var Element */
        $body = $this->et->subElement($envelope, "soapenv:Body");
        /** @var Element */
        $methodElement = $this->et->subElement($body, "bil:CreateRecurringPayment");
        /** @var Element */
        $requestElement = $this->et->subElement($methodElement, "bil:request");

        $this->buildCredentials($requestElement, $credentials);

        /** @var Element */
        $billElement = $this->et->subElement($requestElement, "bdms:Bills");
        $this->buildRecurringBillTransactions($billElement, $schedule->bills);

        /** @var DateTime */
        $endDate = $schedule->endDate;
        $this->buildEndDate($requestElement, $endDate);

        /** @var DateTime */
        $startDate = $schedule->startDate;
        $this->buildFirstInstanceDate($requestElement, $startDate);

        $this->et->subElement($requestElement, "bdms:InitialPaymentMethod", $schedule->initialPaymentMethod);
        /** @var float */
        $scheduleAmount = $schedule->amount;
        $this->et->subElement($requestElement, "bdms:InstancePaymentAmount", $scheduleAmount);
        /** @var int */
        $scheduleNumberOfPayments = $schedule->numberOfPaymentsRemaining;
        $this->et->subElement($requestElement, "bdms:NumberOfInstance", $scheduleNumberOfPayments);
        $this->et->subElement($requestElement, "bdms:OrderID", $schedule->id);

        $lastPrimaryConvenienceAmount = (string)$schedule->lastPrimaryConvenienceAmount;
        if (!$this->isNullOrEmpty($lastPrimaryConvenienceAmount)) {
            $this->et->subElement($requestElement, "bdms:OriginalLastPrimaryConvFeeAmount", $lastPrimaryConvenienceAmount);
        }

        $primaryConvenienceAmount = (string)$schedule->primaryConvenienceAmount;
        if (!$this->isNullOrEmpty($primaryConvenienceAmount)) {
            $this->et->subElement($requestElement, "bdms:OriginalPrimaryConvFeeAmount", $primaryConvenienceAmount);
        }

        if ($schedule->customer !== null) {
            $this->buildPayOrData($requestElement, $schedule->customer);
        }

        if ($schedule->token !== null) {
            $this->et->subElement($requestElement, "bdms:PrimaryAccountToken", $schedule->token);
        } else {
            throw new UnsupportedTransactionException("Primary token is required to perform recurring transaction.");
        }

        $this->et->subElement($requestElement, "bdms:RecurringPaymentAuthorizationType", $schedule->recurringAuthorizationType);

        if ($schedule->frequency !== null) {
            $this->et->subElement($requestElement, "bdms:ScheduleType", $schedule->frequency);
            if ($schedule->frequency === ScheduleFrequency::SEMI_MONTHLY) {
                if ($schedule->secondInstanceDate !== null) {
                    /** @var DateTime */
                    $secondInstanceDate = $schedule->secondInstanceDate;
                    $this->buildSecondInstanceDate($requestElement, $secondInstanceDate);
                } else {
                    throw new UnsupportedTransactionException("Second Instance Date is required for the semi-monthly schedule.");
                }
            }
        } else {
            throw new UnsupportedTransactionException("Schedule Type is required to perform recurring transaction.");
        }

        if (!$this->isNullOrEmpty($schedule->secondaryToken)) {
            $this->et->subElement($requestElement, "bdms:SecondaryAccountToken", $schedule->secondaryToken);
        }

        if (!$this->isNullOrEmpty($schedule->signatureImageInBase64)) {
            $this->et->subElement($requestElement, "bdms:SignatureImage", $schedule->signatureImageInBase64);
        }

        return $this->et->toString($envelope);
    }

    protected function buildRecurringBillTransactions(Element $parent, array $bills) {
        foreach ($bills as $bill) {    
            /** @var Element */
            $billTransaction = $this->et->subElement($parent, "bdms:RecurringPaymentBill");
            $this->et->subElement($billTransaction, "bdms:BillType", $bill->getBillType());
            $this->et->subElement($billTransaction, "bdms:ID1", $bill->getIdentifier1());
            $this->et->subElement($billTransaction, "bdms:ID2", $bill->getIdentifier2());
            $this->et->subElement($billTransaction, "bdms:ID3", $bill->getIdentifier3());
            $this->et->subElement($billTransaction, "bdms:ID4", $bill->getIdentifier4());
            if ($bill->getCustomer() !== null) {
                if ($bill->getCustomer()->address !== null) {
                    $this->et->subElement($billTransaction, "bdms:ObligorAddress", $bill->getCustomer()->address->streetAddress1);
                    $this->et->subElement($billTransaction, "bdms:ObligorCity", $bill->getCustomer()->address->city);
                    $this->et->subElement($billTransaction, "bdms:ObligorCountry", $bill->getCustomer()->address->country);
                    $this->et->subElement($billTransaction, "bdms:ObligorPostalCode", $bill->getCustomer()->address->postalCode);
                    $this->et->subElement($billTransaction, "bdms:ObligorState", $bill->getCustomer()->address->state);
                }
                $this->et->subElement($billTransaction, "bdms:ObligorEmailAddress", $bill->getCustomer()->email);
                $this->et->subElement($billTransaction, "bdms:ObligorFirstName", $bill->getCustomer()->firstName);
                $this->et->subElement($billTransaction, "bdms:ObligorLastName", $bill->getCustomer()->lastName);
                $this->et->subElement($billTransaction, "bdms:ObligorMiddleName", $bill->getCustomer()->middleName);
                if ($bill->getCustomer()->phone !== null) {
                    $this->et->subElement($billTransaction, "bdms:ObligorPhoneNumber", $bill->getCustomer()->phone->number);
                }
            }
        }
    }

    protected function buildPayOrData(Element $parent, Customer $customer) {
        $this->et->subElement($parent, "bdms:PayorBusinessName", $customer->company);
        $this->et->subElement($parent, "bdms:PayorEmailAddress", $customer->email);
        $this->et->subElement($parent, "bdms:PayorFirstName", $customer->firstName);
        $this->et->subElement($parent, "bdms:PayorLastName", $customer->lastName);
        $this->et->subElement($parent, "bdms:PayorMiddleName", $customer->middleName);
        if ($customer->phone !== null) {
            $this->et->subElement($parent, "bdms:PayorPhoneNumber", $customer->phone->number);
            $this->et->subElement($parent, "bdms:PayorPhoneNumberRegionCode", $customer->middleName);
        }
        if ($customer->address !== null) {
            $this->et->subElement($parent, "bdms:PayorAddress", $customer->address->streetAddress1);
            $this->et->subElement($parent, "bdms:PayorCity", $customer->address->city);
            $this->et->subElement($parent, "bdms:PayorCountry", $customer->address->country);
            $this->et->subElement($parent, "bdms:PayorPostalCode", $customer->address->postalCode);
        }
    }

    protected function buildEndDate(Element $parent, DateTime $endDate) 
    {
        $day = str_pad($endDate->format('d'), 2, '0', STR_PAD_LEFT);
        $indexedMonth = (int)$endDate->format('m') - 1;
        $endMonth = str_pad($indexedMonth + 1, 2, '0', STR_PAD_LEFT);
        $year = $endDate->format('Y');

        $this->et->subElement($parent, "bdms:EndDay", $day);
        $this->et->subElement($parent, "bdms:EndMonth", $endMonth);
        $this->et->subElement($parent, "bdms:EndYear", $year);
    }

    protected function buildFirstInstanceDate(Element $parent, DateTime $firstInstanceDate)
    {
        $day = str_pad($firstInstanceDate->format('d'), 2, '0', STR_PAD_LEFT);
        $indexedMonth = (int)$firstInstanceDate->format('m') - 1;
        $firstInstanceMonth = str_pad($indexedMonth + 1, 2, '0', STR_PAD_LEFT);
        $year = $firstInstanceDate->format('Y');

        $this->et->subElement($parent, "bdms:FirstInstanceDay", $day);
        $this->et->subElement($parent, "bdms:FirstInstanceMonth", $firstInstanceMonth);
        $this->et->subElement($parent, "bdms:FirstInstanceYear", $year);
    }

    protected function buildSecondInstanceDate(Element $parent, DateTime $secondInstanceDate)
    {
        $calendar = $secondInstanceDate;
        $day = str_pad($calendar->format('d'), 2, '0', STR_PAD_LEFT);
        $indexedMonth = (int) $calendar->format('m') - 1;
        $secondInstanceMonth = str_pad($indexedMonth + 1, 2, '0', STR_PAD_LEFT);
        $year = $calendar->format('Y');

        $this->et->subElement($parent, "bdms:SecondInstanceDay", $day);
        $this->et->subElement($parent, "bdms:SecondInstanceMonth", $secondInstanceMonth);
        $this->et->subElement($parent, "bdms:SecondInstanceYear", $year);
    }

}