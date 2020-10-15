<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities;

/**
 * Heartland Payment Application response data
 */
class HpaResponse
{
    public $versionNumber;
    public $ecrId;
    public $sipId;
    public $deviceId;
    public $response;
    public $multipleMessage;
    public $resultCode;
    public $resultText;
    public $responseData;
    public $requestId;
    public $isStoredResponse;
    
    // Internal
    public $status;
    public $command;
    public $version;
    
    // Functional
    public $responseCode;
    public $responseText;
    public $gatewayResponseMessage;
    public $transactionId;
    public $terminalRefNumber;
    public $token;
    public $signatureStatus;
    public $signatureData;
    
    // Transactional
    public $transactionType;
    public $maskedCardNumber;
    public $entryMethod;
    public $authorizationCode;
    public $approvalCode;
    public $transactionAmount;
    public $balanceAmountDue;
    public $cardHolderName;
    public $cardBIN;
    public $cardPresent;
    public $expirationDate;
    public $tipAmount;
    public $cashBackAmount;
    public $avsResponseCode;
    public $avsResponseText;
    public $cvvResponseCode;
    public $cvvResponseText;
    public $taxExempt;
    public $taxExemptId;
    public $ticketNumber;
    public $paymentType;
    public $transactionTime;
    public $cardType;
    public $referenceNumber;
    public $partialApproval;
    
    //EOD
    public $reversal;
    public $emvOfflineDecline;
    public $transactionCertificate;
    public $attachment;
    public $sendSAF;
    public $batchClose;
    public $heartBeat;
    public $eMVPDL;
    
    //EBT
    
    /*
     * EBT transaction type (EBT FoodStamp or Cash Benefits)
     */
    public $ebtType;
    
    /*
     * This element is used to inform the POS whether the receipt should print “PIN VERIFIED”
     */
    public $pinVerified;
            
    //EMV
    /*
     * For EMV transactions this element provides the AID of the EMV card so that the POS can
     * print the AID on the receipt
     *
     * Conditional If transaction is EMV
     */
    public $emvApplicationId;
    
    /*
     * This element is the preferred Name of the Application
     * so that the POS can print the application name on the receipt
     *
     * Conditional If transaction is EMV
     */
    public $emvApplicationName;
    
    /*
     * For EMV transactions this element provides the Transaction Verification Results (TVR) register.
     * This may be helpful in understanding why certain EMV transactions are declined
     *
     * Conditional If transaction is EMV
     */
    public $emvTerminalVerificationResults;
    
    /*
     * For EMV transactions this element provides the Transaction Status Information tag (TSI)
     *
     * Conditional If transaction is EMV
     */
    public $emvCardHolderVerificationMethod;
    
    /*
     * For EMV transactions this element provides the cryptogram type (TC, AAR, AAC, or ARQC)
     *
     * Conditional If transaction is EMV
     */
    public $emvCryptogramType;
    
    /*
     * For EMV transactions this element provides the cryptogram
     *
     * Conditional If transaction is EMV
     */
    public $emvCryptogram;
    
    //Gifts
    
    /*
     * This element is only applicable to balance inquiry transactions
     *
     * Numeric, implied decimal point, no dollar sign
     * Value may range from 0 to 9999999, for example, $12.34 would be sent as 1234
     *
     */
    public $availableBalance;
    
    //Send File
    /*
     * Maximum number of characters of file data in hexadecimal ASCII format that
     * can be sent in each subsequent request
     */
    public $maxDataSize;
    
    //Get last Response
    public $lastResponse;
}
