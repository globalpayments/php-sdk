<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use DateTime;
use GlobalPayments\Api\Entities\{Address, Card, Customer, Transaction};
use GlobalPayments\Api\Entities\BillPay\TokenData;

class TokenInformationRequestResponse extends BillPayResponseBase
{
    function map(): Transaction
    {
        $result = new Transaction();
        $address = new Address();
        $cardDetails = new Card();
        $customerData = new Customer();
        $tokenData = new TokenData();

        $tokenDetailsElement = $this->response->get("a:TokenDetails");
        $accountHolderData= $this->response->getAccountHolderData("a:AccountHolderData");

        $address->streetAddress1 = $accountHolderData["Address"];
        $address->city = $accountHolderData["City"];
        $address->state = $accountHolderData["State"];
        $address->postalCode = $accountHolderData["Zip"];
        $address->country = $accountHolderData["Country"] ;

        $customerData->company = $accountHolderData["BusinessName"];
        $customerData->firstName = $accountHolderData["FirstName"];
        $customerData->lastName = $accountHolderData["LastName"];
        $customerData->middleName = $accountHolderData["MiddleName"];
        $customerData->homePhone = $accountHolderData["Phone"];

        $cardDetails->cardHolderName = $accountHolderData["NameOnCard"];
        $cardDetails->cardExpMonth = $tokenDetailsElement->getString("a:ExpirationMonth");
        $cardDetails->cardExpYear = $tokenDetailsElement->getString("a:ExpirationYear");
        $cardDetails->maskedNumberLast4 = $tokenDetailsElement->getString("a:Last4");

        $tokenData->setExpired($tokenDetailsElement->getBool("a:IsExpired"));
        $tokenData->setLastUsedDateUTC($this->dateTimeXMLParser($tokenDetailsElement->getString("a:LastUsedDateUTC")));
        $tokenData->setMerchants($tokenDetailsElement->getMerchantsElementArray("a:Merchants"));
        $tokenData->setSharedTokenWithGroup($tokenDetailsElement->getBool("a:SharedTokenWithGroup"));

        $result->responseCode = $this->response->getString("a:ResponseCode");
        $result->responseMessage = $this->getFirstResponseMessage($this->response);
        $result->address = $address;
        $result->customerData = $customerData;
        $result->cardDetails = $cardDetails;
        $result->cardDetails->brand = $this->getCardType($tokenDetailsElement->getString("a:PaymentMethod"));
        $result->tokenData = $tokenData;
        $result->setPaymentMethodType($this->getPaymentMethodType($tokenDetailsElement->getString("a:PaymentMethod")));
        $result->cardType = $this->getCardType($tokenDetailsElement->getString("a:PaymentMethod"));
        $result->token = $tokenDetailsElement->getString("a:Token");

        return $result;
    }

    private function dateTimeXMLParser(string $xmlDateTimeStr): DateTime
    {
        $formatter = new DateTime($xmlDateTimeStr);

        return $formatter;
    }
}