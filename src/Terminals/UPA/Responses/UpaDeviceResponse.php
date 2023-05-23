<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

class UpaDeviceResponse extends UpaResponseHandler
{
    public function __construct($jsonResponse, $messageId)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse)
    {
        if (!empty($jsonResponse['data']['cmdResult'])) {
            $this->checkResponse($jsonResponse['data']['cmdResult']);
            
            if ($jsonResponse['data']['cmdResult']['result'] === 'Success') {
                $this->deviceResponseCode = '00';
            }
        }
        
        if (!empty($jsonResponse['data']['data'])) {
            $responseMapping = $this->getResponseMapping();
            foreach ($jsonResponse['data']['data'] as $responseData) {
                if (is_array($responseData)) {
                    foreach ($responseData as $key => $value) {
                        $propertyName = !empty($responseMapping[$key]) ? $responseMapping[$key] : $key;
                        $this->{$propertyName} = $value;
                    }
                }
            }
        }
    }

    /*
     * return Array
     *
     * Format [Response text in Json => Property name in UpaResponse class]
     *
     */
    public function getResponseMapping()
    {
        return array(
            //host
            'responseId' => 'responseId',
            'tranNo' => 'terminalRefNumber',
            'respDateTime' => 'responseDateTime',
            'gatewayResponseCode' => 'gatewayResponseCode',
            'gatewayResponseMessage' => 'gatewayResponseMessage',
            'responseCode' => 'responseCode',
            'responseText' => 'responseText',
            'approvalCode' => 'approvalCode',
            'referenceNumber' => 'transactionId',
            'AvsResultCode' => 'avsResultCode',
            'CvvResultCode' => 'cvvResultCode',
            'AvsResultText' => 'avsResultText',
            'CvvResultText' => 'cvvResultText',
            'additionalTipAmount' => 'additionalTipAmount',
            'baseAmount' => 'transactionAmount',
            'tipAmount' => 'tipAmount',
            'taxAmount' => 'taxAmount',
            'cashbackAmount' => 'cashbackAmount',
            'authorizedAmount' => 'authorizedAmount',
            'totalAmount' => 'totalAmount',
            'surcharge' => 'surcharge',
            'tokenRspCode' => 'tokenResponseCode',
            'tokenRspMsg' => 'tokenResponseMsg',
            'tokenValue' => 'token',
            'txnDescriptor' => 'transactionDescriptor',
            'recurringDataCode' => 'recurringDataCode',
            'CavvResultCode' => 'cavvResultCode',
            'tokenPANLast' => 'tokenPANLast',
            'partialApproval' => 'partialApproval',
            'traceNumber' => 'traceNumber',
            'balanceDue' => 'balanceAmount',
            'baseDue' => 'baseDue',
            'taxDue' => 'taxDue',
            'tipDue' => 'tipDue',
            'cardBrandTransId' => 'cardBrandTransId',

            //payment
            'cardHolderName' => 'cardHolderName',
            'cardType' => 'cardType',
            'cardGroup' => 'cardGroup',
            'ebtType' => 'ebtType',
            'cardAcquisition' => 'entryMethod',
            'maskedPan' => 'maskedCardNumber',
            'signatureLine' => 'signatureStatus',
            'PinVerified' => 'pinVerified',
            'QpsQualified' => 'qpsQualified',
            'storeAndForward' => 'storeAndForward',
            'clerkId' => 'clerkId',
            'invoiceNbr' => 'invoiceNumber',

            //EMV
            '4F' => 'applicationIdentifier',
            '50' => 'applicationLabel',
            '5F20' => 'EmvCardholderName',
            '5F2A' => 'transactionCurrencyCode',
            '5F34' => 'applicationPAN',
            '82' => 'applicationAIP',
            '84' => 'dedicatedDF',
            '8A' => 'authorizedResponse',
            '95' => 'terminalVerificationResults',
            '99' => 'transactionPIN',
            '9A' => 'transactionDate',
            '9B' => 'transactionTSI',
            '9C' => 'transactionType',
            '9F02' => 'amountAuthorized',
            '9F03' => 'otherAmount',
            '9F06' => 'applicationId',
            '9F08' => 'applicationICC',
            '9F0D' => 'applicationIAC',
            '9F0E' => 'IACDenial',
            '9F0F' => 'IACOnline',
            '9F10' => 'issuerApplicationData',
            '9F12' => 'applicationPreferredName',
            '9F1A' => 'terminalCountryCode',
            '9F1E' => 'IFDSerialNumber',
            '9F26' => 'applicationCryptogram',
            '9F27' => 'applicationCryptogramType',
            '9F33' => 'terminalCapabilities',
            '9F35' => 'terminalType',
            '9F36' => 'applicationTransactionCounter',
            '9F37' => 'unpredictableNumber',
            '9F40' => 'additionalTerminalCapabilities',
            '9F41' => 'transactionSequenceCounter',
            'TacDefault' => 'tacDefault',
            'TacDenial' => 'tacDenial',
            'TacOnline' => 'tacOnline',
            '9F34' => 'cardHolderVerificationMethod',
            'batchId' => 'batchId',
            'availableBalance' => 'availableBalance'
        );
    }
}
