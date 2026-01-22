<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities;

/**
 * Global Payments Application response data
 */
class HpaResponse
{
    public ?string $versionNumber = null;
    public ?string $ecrId = null;
    public ?string $sipId = null;
    public ?string $deviceId = null;
    public ?string $response = null;
    public ?string $multipleMessage = null;
    public ?string $resultCode = null;
    public ?string $resultText = null;
    public ?string $responseData = null;
    public ?string $requestId = null;
    public ?bool $isStoredResponse = null;
    
    // Internal
    public ?string $status = null;
    public ?string $command = null;
    public ?string $version = null;
    
    // Functional
    public ?string $responseCode = null;
    public ?string $responseText = null;
    public ?string $gatewayResponseMessage = null;
    public ?string $transactionId = null;
    public ?string $terminalRefNumber = null;
    public ?string $token = null;
    public ?string $signatureStatus = null;
    public mixed $signatureData = null;
    
    // Transactional
    public ?string $transactionType = null;
    public ?string $maskedCardNumber = null;
    public ?string $entryMethod = null;
    public ?string $authorizationCode = null;
    public ?string $approvalCode = null;
    public float|int|string|null $transactionAmount = null;
    public float|int|string|null $balanceAmountDue = null;
    public ?string $cardHolderName = null;
    public ?string $cardBIN = null;
    public ?bool $cardPresent = null;
    public ?string $expirationDate = null;
    public float|int|string|null $tipAmount = null;
    public float|int|string|null $cashBackAmount = null;
    public ?string $avsResponseCode = null;
    public ?string $avsResponseText = null;
    public ?string $cvvResponseCode = null;
    public ?string $cvvResponseText = null;
    public ?bool $taxExempt = null;
    public ?string $taxExemptId = null;
    public ?string $ticketNumber = null;
    public ?string $paymentType = null;
    public ?string $transactionTime = null;
    public ?string $cardType = null;
    public ?string $referenceNumber = null;
    public ?bool $partialApproval = null;
    
    //EOD
    public ?string $reversal = null;
    public ?string $emvOfflineDecline = null;
    public ?string $transactionCertificate = null;
    public ?string $attachment = null;
    public ?string $sendSAF = null;
    public ?string $batchClose = null;
    public ?string $heartBeat = null;
    public ?string $eMVPDL = null;
    
    //EBT
    
    /*
     * EBT transaction type (EBT FoodStamp or Cash Benefits)
     */
    public ?string $ebtType = null;
    
    /*
     * This element is used to inform the POS whether the receipt should print "PIN VERIFIED"
     */
    public ?bool $pinVerified = null;
            
    //EMV
    /*
     * For EMV transactions this element provides the AID of the EMV card so that the POS can
     * print the AID on the receipt
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvApplicationId = null;
    
    /*
     * This element is the preferred Name of the Application
     * so that the POS can print the application name on the receipt
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvApplicationName = null;
    
    /*
     * For EMV transactions this element provides the Transaction Verification Results (TVR) register.
     * This may be helpful in understanding why certain EMV transactions are declined
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvTerminalVerificationResults = null;
    
    /*
     * For EMV transactions this element provides the Transaction Status Information tag (TSI)
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvCardHolderVerificationMethod = null;
    
    /*
     * For EMV transactions this element provides the cryptogram type (TC, AAR, AAC, or ARQC)
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvCryptogramType = null;
    
    /*
     * For EMV transactions this element provides the cryptogram
     *
     * Conditional If transaction is EMV
     */
    public ?string $emvCryptogram = null;
    
    //Gifts
    
    /*
     * This element is only applicable to balance inquiry transactions
     *
     * Numeric, implied decimal point, no dollar sign
     * Value may range from 0 to 9999999, for example, $12.34 would be sent as 1234
     *
     */
    public float|int|string|null $availableBalance = null;
    
    //Send File
    /*
     * Maximum number of characters of file data in hexadecimal ASCII format that
     * can be sent in each subsequent request
     */
    public ?int $maxDataSize = null;
    
    //Get last Response
    public ?string $lastResponse = null;
}
