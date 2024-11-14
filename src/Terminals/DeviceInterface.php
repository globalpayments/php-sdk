<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceScreen;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceUpdatable;
use GlobalPayments\Api\Terminals\Abstractions\ISAFResponse;
use GlobalPayments\Api\Terminals\Abstractions\ISignatureResponse;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\Entities\GenericData;
use GlobalPayments\Api\Terminals\Entities\MessageLines;
use GlobalPayments\Api\Terminals\Entities\PrintData;
use GlobalPayments\Api\Terminals\Entities\PromptData;
use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Entities\ScanData;
use GlobalPayments\Api\Terminals\Entities\UDData;
use GlobalPayments\Api\Terminals\Enums\BatchReportType;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Enums\DeviceConfigType;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\PromptType;
use GlobalPayments\Api\Terminals\UPA\Entities\POSData;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;

abstract class DeviceInterface implements IDeviceInterface
{
    /** @var DeviceController */
    public DeviceController $controller;

    public ValidationRequest $validations;

    /** @var RequestIdProvider */
    public $requestIdProvider;

    public ?string $ecrId = null;

    private const ERROR_MESSAGE = "This method is not supported by the currently configured device.";

    public function __construct(DeviceController $controller)
    {
        $this->controller = $controller;
        $this->requestIdProvider = $controller->requestIdProvider;
        $this->validations = new ValidationRequest();
    }

    public function cancel($cancelParams = null)
    {
        throw new UnsupportedTransactionException(
            self::ERROR_MESSAGE
        );
    }

    public function closeLane() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function disableHostResponseBeep() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getSignatureFile(SignatureData $data = null) : ISignatureResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function registerPOS(POSData $data) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function broadcastConfiguration(bool $enable) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getDeviceConfig(string|DeviceConfigType $type) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function setDebugLevel(array $debugLevels,string $logOutput = null) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getDebugLevel() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getDebugInfo(string $logDirectory, string $fileIndicator = null) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function scan(ScanData $data) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function print(PrintData $data) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function initialize()
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function lineItem(
        string $leftText,
        string $rightText = null,
        string $runningLeftText = null,
        string $runningRightText = null
    ) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function openLane() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function promptForSignature(string $transactionId = null)
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function reboot() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function ping(): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getAppInfo(): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function clearDataLake(): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function setTimeZone(string $timezone): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getParam(array $params = []): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function communicationCheck() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function logon() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function startCard(PaymentMethodType $paymentMethodType) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    /**
     * @param string $language
     * @return DeviceResponse
     *
     * @throws UnsupportedTransactionException
     */
    public function removeCard(string $language = ''): DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function sendSaf($safIndicator = null) : DeviceResponse
    {
            throw new NotImplementedException();
    }

    public function sendStoreAndForward() : ISAFResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function reset(): DeviceResponse
    {
        throw new NotImplementedException();
    }

    public function returnToIdle() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function loadUDData(UDData $screen) : IDeviceScreen
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function removeUDData(UDData $screen) : IDeviceScreen
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function executeUDData(UDData $screen) : IDeviceScreen
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function injectUDData(UDData $screen) : IDeviceScreen
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function enterPIN(PromptMessages $promptMessages, bool $canBypass, string $accountNumber) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getGenericEntry(GenericData $data) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function displayMessage(MessageLines $messageLines) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    /**
     * @param string|DisplayOption $option
     * @return DeviceResponse
     * @throws UnsupportedTransactionException
     */
    public function returnDefaultScreen(string $option) : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getEncryptionType() : DeviceResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }
    /********* END ADMIN METHODS ********/

    /**********START Batching ************/

    public function endOfDay()
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function batchClose(): IBatchCloseResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getLastEOD() : IBatchCloseResponse
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    /********* END Batching ********/

    /**********START Transactions ************/

    /**
     * @return TerminalAuthBuilder
     */
    public function addValue($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::ADD_VALUE, PaymentMethodType::GIFT))
                ->withAmount($amount)
                ->withCurrency(CurrencyType::CURRENCY);
    }

    public function authorize($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function balance() : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::BALANCE, PaymentMethodType::GIFT))
            ->withCurrency(CurrencyType::CURRENCY);
    }

    public function capture($amount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::CAPTURE, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function refund($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::CREDIT))
                ->withAmount($amount);
    }

    public function sale($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function verify() : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::VERIFY, PaymentMethodType::CREDIT));
    }

    public function void() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    public function withdrawal($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::BENEFIT_WITHDRAWAL, PaymentMethodType::EBT))
                ->withAmount($amount);
    }

    public function startTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function continueTransaction(float $amount, bool $isEmv = false) : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function completeTransaction() : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function prompt(string $promptType, PromptData $promptData): DeviceResponse
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function processTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function tipAdjust($amount = null): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function tokenize(): TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function deletePreAuth() : TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function increasePreAuth($amount) : TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function refundById(string $transactionId): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function reverse() : TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function updateLodgingDetails(float $amount): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function updateTaxInfo(?float $amount = null): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getSAFReport() : TerminalReportBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function getBatchReport(): TerminalReportBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function getBatchDetails(?string $batchId = null,bool $printReport = false, string|BatchReportType $reportType = null): ITerminalReport
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function findBatches() : TerminalReportBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    public function getOpenTabDetails() : TerminalReportBuilder
    {
        throw new UnsupportedTransactionException(self::ERROR_MESSAGE);
    }

    /****************************************END*************************************/

    public function safDelete($safIndicator)
    {
        // TODO: Implement safDelete() method.
    }

    public function startDownload($deviceSettings)
    {
        // TODO: Implement startDownload() method.
    }

    public function setSafMode($paramValue)
    {
        // TODO: Implement setSafMode() method.
    }

    public function safSummaryReport($safIndicator = null)
    {
        // TODO: Implement safSummaryReport() method.
    }

    public function sendFile($sendFileData)
    {
        // TODO: Implement sendFile() method.
    }

    public function getDiagnosticReport($totalFields)
    {
        // TODO: Implement getDiagnosticReport() method.
    }

    public function getLastResponse()
    {
        // TODO: Implement getLastResponse() method.
    }
}