<?php

namespace GlobalPayments\Api\Terminals\Interfaces;

interface IDeviceInterface
{

    // Admin Calls
    public function cancel();

    public function closeLane();

    public function disableHostResponseBeep();

    public function getSignatureFile();

    public function initialize();

    public function openLane();

    public function reboot();

    public function reset();
    
    public function lineItem($lineItemDetails);
    
    public function startCard($paymentMethodType = null);

    // Batch Calls
    public function batchClose();
    public function eod();

    //Credit Calls
    public function creditAuth($amount = null);

    public function creditCapture($amount = null);

    public function creditRefund($amount = null);

    public function creditSale($amount = null);

    public function creditVerify();

    public function creditVoid();

    //Debit Calls
    public function debitSale($amount = null);

    public function debitRefund($amount = null);
    
    //EBT Calls
    public function ebtBalance();

    public function ebtPurchase($amount = null);
    
    public function ebtRefund($amount = null);
    
    public function ebtWithdrawl($amount = null);
    
    public function startDownload($deviceSettings);
    
    //Gift calls
    public function giftSale($amount = null);
    
    public function giftAddValue($amount = null);
    
    public function giftVoid();
    
    public function giftBalance();
    
    //SAF mode
    public function setSafMode($paramValue);
    public function sendSaf($safIndicator = null);
    public function safDelete($safIndicator);
    
    //send file request
    public function sendFile($sendFileData);

    //Get Reports
    public function getDiagnosticReport($totalFields);
    public function getLastResponse();
    
    public function promptForSignature($transactionId = null);
}
