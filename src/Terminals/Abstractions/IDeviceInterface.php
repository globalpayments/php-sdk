<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\DeviceResponse;

interface IDeviceInterface
{
    // Admin Calls
    public function lineItem(
        string $leftText,
        string $rightText = null,
        string $runningLeftText = null,
        string $runningRightText = null
    ) : DeviceResponse;

    public function cancel($cancelParams = null);

    public function reboot() : DeviceResponse;

    public function sale($amount = null) : TerminalAuthBuilder;

    public function capture($amount = null) : TerminalManageBuilder;

    public function verify() : TerminalAuthBuilder;

    public function authorize($amount = null) : TerminalAuthBuilder;

    public function void() : TerminalManageBuilder;

    public function closeLane() : DeviceResponse;

    public function disableHostResponseBeep() : DeviceResponse;

    public function getSignatureFile();

    public function initialize();

    public function openLane() : DeviceResponse;

    public function promptForSignature(string $transactionId = null);

    public function reset() : DeviceResponse;

    public function startCard(PaymentMethodType $paymentMethodType) : DeviceResponse;

    public function sendSaf($safIndicator = null) : DeviceResponse;

    public function batchClose();

    public function endOfDay();

    public function addValue($amount = null) : TerminalAuthBuilder;

    public function balance() : TerminalAuthBuilder;

    public function refund($amount = null) : TerminalAuthBuilder;

    public function withdrawal($amount = null): TerminalAuthBuilder;

    public function tipAdjust($amount = null) : TerminalManageBuilder;

    public function tokenize() : TerminalAuthBuilder;

    /**********************************************************/

    //EBT Calls

    public function startDownload($deviceSettings);
    
    //Gift calls

    //SAF mode
    public function setSafMode($paramValue);
    public function safDelete($safIndicator);
    public function safSummaryReport($safIndicator = null);

    //send file request
    public function sendFile($sendFileData);

    //Get Reports
    public function getDiagnosticReport($totalFields);
    public function getLastResponse();
}
