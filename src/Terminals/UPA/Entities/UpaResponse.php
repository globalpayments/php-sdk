<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

use GlobalPayments\Api\Terminals\UPA\Responses\UpaResponseHandler;

/**
 * Heartland Upa response data
 */
class UpaResponse extends UpaResponseHandler
{
    //host
    public $responseId;
    public $transactionId;
    public $responseDateTime;
    public $gatewayResponseCode;
    public $gatewayResponseMessage;
    public $responseCode;
    public $responseText;
    public $approvalCode;
    public $avsResultCode;
    public $cvvResultCode;
    public $avsResultText;
    public $cvvResultText;
    public $additionalTipAmount;
    public $transactionAmount;
    public $tipAmount;
    public $taxAmount;
    public $cashbackAmount;
    public $authorizedAmount;
    public $totalAmount;
    public $surcharge;
    public $tokenResponseCode;
    public $tokenResponseMsg;
    public $token;
    public $transactionDescriptor;
    public $recurringDataCode;
    public $cavvResultCode;
    public $tokenPANLast;
    public $partialApproval;
    public $traceNumber;
    public $balanceAmount;
    public $baseDue;
    public $taxDue;
    public $tipDue;
    public $availableBalance;
    public $terminalRefNumber;
    
    //payment
    public $cardHolderName;
    public $cardType;
    public $cardGroup;
    public $ebtType;
    public $entryMethod;
    public $maskedCardNumber;
    public $signatureStatus;
    public $pinVerified;
    public $qpsQualified;
    public $storeAndForward;
    public $clerkId;
    public $invoiceNumber;
    
    //EMV
    public $applicationIdentifier; //4F
    public $applicationLabel; //50
    public $EmvCardholderName; //5F20
    public $transactionCurrencyCode; //5F2A
    public $applicationPAN; //5F34
    public $applicationAIP; //82
    public $dedicatedDF; //84
    public $authorizedResponse; //8A
    public $terminalVerificationResults; //95
    public $transactionPIN; //99
    public $transactionDate; //9A
    public $transactionTSI; //9B
    public $transactionType; //9C
    public $amountAuthorized; //9F02
    public $otherAmount; //9F03
    public $applicationId; //9F06
    public $applicationICC; //9F08
    public $applicationIAC; //9F0D
    public $IACDenial; //9F0E
    public $IACOnline; //9F0F
    public $issuerApplicationData; //9F10
    public $applicationPreferredName; //9F12
    public $terminalCountryCode; //9F1A
    public $IFDSerialNumber; //9F1E
    public $applicationCryptogram; //9F26
    public $applicationCryptogramType; //9F27
    public $terminalCapabilities; //9F33
    public $terminalType; //9F35
    public $applicationTransactionCounter; //9F36
    public $unpredictableNumber; //9F37
    public $additionalTerminalCapabilities; //9F40
    public $transactionSequenceCounter; //9F41
    public $tacDefault; //TacDefault
    public $tacDenial; //TacDenial
    public $tacOnline; //TacOnline
    public $customerVerificationMethod; //9F34
    
    //EOD
    public $batchId;
    
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
            '9F34' => 'customerVerificationMethod',
            'batchId' => 'batchId',
            'availableBalance' => 'availableBalance'
        );
    }
}
