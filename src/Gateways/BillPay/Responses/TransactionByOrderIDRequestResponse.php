<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\{Address, Customer};
use GlobalPayments\Api\Entities\BillPay\{
    AuthorizationRecord,
    Bill
};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Utils\Element;

class TransactionByOrderIDRequestResponse extends BillPayResponseBase
{
    function map()
    {
        $acceptedCodes = ['00', '0'];
        $responseCode = $this->response->getString('a:ResponseCode');
        $responseMessage = $this->getFirstResponseMessage($this->response);

        $transaction = $this->response->get('a:Transaction');

        // Check gateway responses
        if (!in_array($responseCode, $acceptedCodes)) {
            throw new GatewayException(
                sprintf("Unexpected Gateway Response: %s - %s", $responseCode, $responseMessage),
                $responseCode,
                $responseMessage
            );
        }

        $billTransactionsElement = $transaction->getTransactionSummaryElement('a:BillTransactions');
        $authorizationsElement = $transaction->getTransactionSummaryElement('a:Authorizations');

        $transactionSummary = new TransactionSummary();
        $transactionSummary->amount = $transaction->getFloat('a:Amount');
        $transactionSummary->application = $transaction->getString('a:Application');

        $authorizationRecords = $this->populateAuthorizationRecordsFromElement($authorizationsElement);
        if ($authorizationRecords !== null) {
            $transactionSummary->authorizationRecords = [];
            array_push($transactionSummary->authorizationRecords, $authorizationRecords);
        }
        $billTransactions = $this->populateBillTransactionsFromElement($billTransactionsElement);
        if($billTransactions !== null) {
            $transactionSummary->billTransactions = [];
            array_push($transactionSummary->billTransactions, $billTransactions);
        }
        $transactionSummary->feeAmount = $transaction->getFloat('a:FeeAmount');
        $transactionSummary->merchantInvoiceNumber = $transaction->getString('a:MerchantInvoiceNumber');
        $transactionSummary->merchantName = $transaction->getString('a:MerchantName');
        $transactionSummary->merchantPONumber = $transaction->getString('a:MerchantPONumber');
        $transactionSummary->merchantTransactionDescription = $transaction->getString('a:MerchantTransactionDescription');
        $transactionSummary->merchantTransactionID = $transaction->getString('a:MerchantTransactionID');
        $transactionSummary->netAmount = $transaction->getString('a:NetAmount');
        $transactionSummary->netFeeAmount = $transaction->getString('a:NetFeeAmount');
        $transactionSummary->payorData = $this->populatePayorData($transaction);
        $transactionSummary->transactionDate = $transaction->getDateTime('a:TransactionDate');
        $transactionSummary->transactionId = $transaction->getInt('a:TransactionID');
        $transactionSummary->transactionType = $transaction->getString('a:TransactionType');
        $transactionSummary->username = $transaction->getString('a:UserName');

        return $transactionSummary;
    }

    private function populatePayorData(Element $transactionElement) : Customer
    {
        $newCustomer = new Customer();

        $address = new Address();
        $address->streetAddress1 = $transactionElement->getString('a:PayorAddress');
        $address->city = $transactionElement->getString('a:PayorCity');
        $address->country = $transactionElement->getString('a:PayorCountry');
        $address->postalCode = $transactionElement->getString('a:PayorPostalCode');
        $address->state = $transactionElement->getString('a:PayorState');

        $newCustomer->address = $address;
        $newCustomer->company = $transactionElement->getString('a:PayorBusinessName');
        $newCustomer->email = $transactionElement->getString('a:PayorEmailAddress');
        $newCustomer->firstName = $transactionElement->getString('a:PayorFirstName');
        $newCustomer->lastName = $transactionElement->getString('a:PayorLastName');
        $newCustomer->middleName = $transactionElement->getString('a:PayorMiddleName');
        $newCustomer->workPhone = $transactionElement->getString('a:PayorPhoneNumber');

        return $newCustomer;
    }

    private function populateBillTransactionsFromElement(Element $billTransactionsElement) : array|null
    {
        if ($billTransactionsElement->getElement()->childNodes->length > 0) {
            $billTransactionsList = [];

            /** @var Element $bill */
            foreach($billTransactionsElement->getAll('a:BillTransactionRecord') as $bill) {
                $newBill = new Bill();
                $newBill->setBillType($bill->getString('a:BillType'));
                $newBill->setIdentifier1($bill->getString('a:ID1'));
                $newBill->setIdentifier2($bill->getString('a:ID2'));
                $newBill->setIdentifier3($bill->getString('a:ID3'));
                $newBill->setIdentifier4($bill->getString('a:ID4'));
                $newBill->setAmount($bill->getFloat('a:AmountToApplyToBill'));

                $address = new Address();
                $address->streetAddress1 = $bill->getString('a:ObligorAddress');
                $address->city = $bill->getString('a:ObligorCity');
                $address->country = $bill->getString('a:ObligorCountry');
                $address->postalCode = $bill->getString('a:ObligorPostalCode');
                $address->state = $bill->getString('a:ObligorState');

                $customer = new Customer();
                $customer->address = $address;
                $customer->email = $bill->getString('a:ObligorEmailAddress');
                $customer->firstName = $bill->getString('a:ObligorFirstName');
                $customer->lastName = $bill->getString('a:ObligorLastName');
                $customer->middleName = $bill->getString('a:ObligorMiddleName');
                $customer->workPhone = $bill->getString('a:ObligorPhoneNumber');

                $newBill->setCustomer($customer);

                array_push($billTransactionsList, $newBill);
            }
            return $billTransactionsList;
        }
        return null;
    }

    private function populateAuthorizationRecordsFromElement(Element $authorizationsElement) : array|null
    {
        if ($authorizationsElement->getElement()->childNodes->length > 0) {
            $authorizationRecordsList = [];

            /** @var Element $record */
            foreach($authorizationsElement->getAll('a:AuthorizationRecord') as $record) {
                $authRecord = new AuthorizationRecord();
                $authRecord->addToBatchReferenceNumber = $record->getString('a:AddToBatchReferenceNumber');
                $authRecord->amount = $record->getFloat('a:Amount');
                $authRecord->authCode = $record->getString('a:AuthCode');
                $authRecord->authorizationType = $record->getString('a:AuthorizationType');
                $authRecord->avsResultCode = $record->getString('a:AvsResultCode');
                $authRecord->avsResultText = $record->getString('a:AvsResultText');
                $authRecord->cardEntryMethod = $record->getString('a:CardEntryMethod');
                $authRecord->cvvResultCode = $record->getString('a:CvvResultCode');
                $authRecord->cvvResultText = $record->getString('a:AvsResultText');
                $authRecord->emvApplicationCryptogram = $record->getString('a:EmvApplicationCryptogram');
                $authRecord->emvApplicationCryptogramType = $record->getString('a:EmvApplicationCryptogramType');
                $authRecord->emvApplicationID = $record->getString('a:EmvApplicationID');
                $authRecord->emvApplicationName = $record->getString('a:EmvApplicationName');
                $authRecord->emvCardholderVerificationMethod = $record->getString('a:EmvCardholderVerificationMethod');
                $authRecord->emvIssuerResponse = $record->getString('a:EmvIssuerResponse');
                $authRecord->emvSignatureRequired = $record->getString('a:EmvSignatureRequired');
                $authRecord->gateway = $record->getString('a:Gateway');
                $authRecord->gatewayBatchID = $record->getString('a:GatewayBatchID');
                $authRecord->gatewayDescription = $record->getString('a:GatewayDescription');
                $authRecord->maskedAccountNumber = $record->getString('a:MaskedAccountNumber');
                $authRecord->maskedRoutingNumber = $record->getString('a:MaskedRoutingNumber');
                $authRecord->paymentMethod = $record->getString('a:PaymentMethod');
                $authRecord->referenceNumber = $record->getString('a:ReferenceNumber');
                $authRecord->routingNumber = $record->getString('a:RoutingNumber');
                $authRecord->netAmount = $record->getFloat('a:NetAmount');

                // We are taking this approach for the integers because if the value is null, GetValue is failing the cast
                $refAuthID = $record->getString('a:ReferenceAuthorizationID');
                $authRecord->referenceAuthorizationID = ($refAuthID !== '' && $refAuthID !== null) ? (int)$authRecord : null;

                $authID = $record->getString('a:AuthorizationID');
                $authRecord->authorizationID = ($authID !== '' && $authID !== null) ? (int)$authID : null;

                $originalAuthID = $record->getString('a:OriginalAuthorizationID');
                $authRecord->originalAuthorizationID = ($originalAuthID !== '' && $originalAuthID !== null) ? (int)$originalAuthID : null;

                array_push($authorizationRecordsList, $authRecord);
            }
            
            return $authorizationRecordsList;
        }
        
        return null;
    }
}