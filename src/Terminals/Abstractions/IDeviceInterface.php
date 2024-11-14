<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\DeviceResponse;
use GlobalPayments\Api\Terminals\Entities\GenericData;
use GlobalPayments\Api\Terminals\Entities\MessageLines;
use GlobalPayments\Api\Terminals\Entities\PrintData;
use GlobalPayments\Api\Terminals\Entities\PromptData;
use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Entities\ScanData;
use GlobalPayments\Api\Terminals\Entities\UDData;
use GlobalPayments\Api\Terminals\Enums\BatchReportType;
use GlobalPayments\Api\Terminals\Enums\DeviceConfigType;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\PromptType;
use GlobalPayments\Api\Terminals\UPA\Entities\POSData;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;

interface IDeviceInterface
{
    public function sale($amount = null) : TerminalAuthBuilder;

    public function verify() : TerminalAuthBuilder;

    public function authorize($amount = null) : TerminalAuthBuilder;

    public function addValue($amount = null) : TerminalAuthBuilder;

    public function balance() : TerminalAuthBuilder;

    public function refund($amount = null) : TerminalAuthBuilder;

    public function withdrawal($amount = null): TerminalAuthBuilder;

    public function tokenize() : TerminalAuthBuilder;

    public function startTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder;

    /**
     * @param float $amount - The total amount of the transaction, which should be the sum of the base amount, surcharge,
     *                        cashback, tip and tax amounts.
     * @param bool $isEmv - indicates if it's a traditional EMV transaction
     * @return TerminalAuthBuilder
     */
    public function continueTransaction(float $amount, bool $isEmv = false) : TerminalAuthBuilder;
    public function completeTransaction() : TerminalAuthBuilder;

    public function processTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder;

    /********************************************************************************/

    public function void() : TerminalManageBuilder;

    public function capture($amount = null) : TerminalManageBuilder;

    public function tipAdjust($amount = null) : TerminalManageBuilder;

    public function deletePreAuth() : TerminalManageBuilder;

    public function increasePreAuth($amount) : TerminalManageBuilder;

    public function reverse() :  TerminalManageBuilder;

    public function updateTaxInfo(?float $amount = null) : TerminalManageBuilder;

    public function updateLodgingDetails(float $amount) : TerminalManageBuilder;

    /********************************************************************************/
    public function lineItem(
        string $leftText,
        string $rightText = null,
        string $runningLeftText = null,
        string $runningRightText = null
    ) : DeviceResponse;

    public function reboot() : DeviceResponse;

    public function closeLane() : DeviceResponse;

    public function disableHostResponseBeep() : DeviceResponse;

    public function openLane() : DeviceResponse;

    public function reset() : DeviceResponse;

    public function startCard(PaymentMethodType $paymentMethodType) : DeviceResponse;

    public function sendSaf($safIndicator = null) : DeviceResponse;
    public function sendStoreAndForward() : ISAFResponse;

    public function ping() : DeviceResponse;

    public function getAppInfo() : DeviceResponse;

    public function clearDataLake() : DeviceResponse;

    public function setTimeZone(string $timezone) : DeviceResponse;

    public function getParam(array $params = []): DeviceResponse;

    public function removeCard(string $language = '') : DeviceResponse;

    public function enterPIN(PromptMessages $promptMessages, bool $canBypass, string $accountNumber) : DeviceResponse;
    /**
     * This command is used to check the Host Credentials
     *
     * @return DeviceResponse
     */
    public function communicationCheck() : DeviceResponse;

    /**
     * This command is used for testing the host connection by sending a test Credit Sale transaction.
     * @return DeviceResponse
     */
    public function logon() : DeviceResponse;

    /********************************************************************************/

    public function cancel($cancelParams = null);

    public function getSignatureFile(SignatureData $data = null) : ISignatureResponse;

    public function initialize();

    public function promptForSignature(string $transactionId = null);

    public function batchClose() : IBatchCloseResponse;

    public function endOfDay();

    /**
     * This command is used to obtain the last EODProcessing result, including that from an Auto EOD.
     *
     * @return IBatchCloseResponse
     */
    public function getLastEOD() : IBatchCloseResponse;

    public function registerPOS(POSData $data) : DeviceResponse;

    public function broadcastConfiguration(bool $enable) : DeviceResponse;

    public function getDeviceConfig(string|DeviceConfigType $type) : DeviceResponse;

    /**
     * @param array $debugLevels List of DebugLevel
     * @param string|null $logOutput Value of DebugLogsOutput
     *
     * @return DeviceResponse
     */
    public function setDebugLevel(array $debugLevels, string $logOutput = null) : DeviceResponse;

    public function getDebugLevel() : DeviceResponse;

    /**
     * This command is used to get the debug information.
     *
     * @param string $logDirectory
     * @param string|null $fileIndicator
     * @return DeviceResponse
     */
    public function getDebugInfo(string $logDirectory, string $fileIndicator = null) : DeviceResponse;

    /**
     * This command informs the application to display the idle screen.
     * @return DeviceResponse
     */
    public function returnToIdle() : DeviceResponse;

    /***** START commands for user defined screen of the device ***/

    /**
     * This command is used to load different kinds of user-defined screens to the device.
     * @param UDData $screen
     * @return IDeviceScreen
     */
    public function loadUDData(UDData $screen) : IDeviceScreen;

    /**
     * This command is used to remove a previously loaded user-defined screen from the slot indicated.
     * @param UDData $screen
     * @return IDeviceScreen
     */
    public function removeUDData(UDData $screen) : IDeviceScreen;

    /**
     * This command will display the file indicated, which is previously loaded with the LoadUDDataFile.
     * @param UDData $screen
     * @return IDeviceScreen
     */
    public function executeUDData(UDData $screen) : IDeviceScreen;

    /**
     * @param UDData $screen
     * @return IDeviceScreen
     */
    public function injectUDData(UDData $screen) : IDeviceScreen;

    /***** END commands for user defined screen of the device ***/

    /**
     * @param ScanData $data
     * @return DeviceResponse
     */
    public function scan(ScanData $data) : DeviceResponse;

    /**
     * @param PrintData $data
     * @return DeviceResponse
     */
    public function print(PrintData $data) : DeviceResponse;

    /**
     * @param PromptType $promptType
     * @param PromptData $promptData
     * @return DeviceResponse
     */
    public function prompt(string $promptType, PromptData $promptData): DeviceResponse;

    public function getGenericEntry(GenericData $data) : DeviceResponse;

    public function displayMessage(MessageLines $messageLines) : DeviceResponse;

    /**
     * @param string|DisplayOption $option
     * @return DeviceResponse
     */
    public function returnDefaultScreen(string $option) : DeviceResponse;

    public function getEncryptionType() : DeviceResponse;

    /********************************************************************************/

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

    public function getSAFReport() : TerminalReportBuilder;
    public function getBatchReport() : TerminalReportBuilder;
    public function getBatchDetails(?string $batchId = null, bool $printReport = false, string|BatchReportType $reportType = null) : ITerminalReport;
    public function findBatches() : TerminalReportBuilder;
    public function getOpenTabDetails() : TerminalReportBuilder;
}
