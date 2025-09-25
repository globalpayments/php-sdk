<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Utils\ArrayUtils;
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType, TransactionModifier, TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\{ApiException,
    ArgumentException,
    MessageException,
    UnsupportedTransactionException};
use GlobalPayments\Api\Terminals\{
    DeviceInterface, TerminalUtils, DeviceResponse
};
use GlobalPayments\Api\Terminals\Abstractions\{IBatchCloseResponse,
    IDeviceScreen,
    ISAFResponse,
    ISignatureResponse,
    ITerminalReport};
use Symfony\Component\Console\Terminal;
use GlobalPayments\Api\Terminals\Builders\{
    TerminalAuthBuilder, TerminalManageBuilder, TerminalReportBuilder
};

use GlobalPayments\Api\Terminals\Enums\{BatchReportType,
    DebugLevel,
    DeviceConfigType,
    DisplayOption,
    PromptType,
    TerminalReportType,
    ReportOutput};

use GlobalPayments\Api\Terminals\Entities\{GenericData,
    MessageLines,
    PrintData,
    PromptData,
    PromptMessages,
    ScanData,
    UDData};

use GlobalPayments\Api\Terminals\UPA\Entities\{
    SignatureData, POSData
};
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\{
    UpaMessageId, UpaSearchCriteria
};

use GlobalPayments\Api\Terminals\UPA\Responses\{SignatureResponse,
    UDScreenResponse,
    UpaBatchReport,
    TransactionResponse,
    TerminalSetupResponse,
    UpaSAFResponse};

/**
 * Heartland payment application implementation of device messages
 */
class UpaInterface extends DeviceInterface
{
    public UpaController $upaController;

    public function __construct(UpaController $deviceController)
    {
        $this->upaController = $deviceController;
        parent::__construct($deviceController);
    }

    #region Admin Messages

    public function batchClose() : IBatchCloseResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::EOD,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::EOD);

        return new TransactionResponse($rawResponse);
    }

    public function cancel($cancelParams = null)
    {
        $data = [];
        if (!empty($cancelParams->displayOption)) {
            $data['params']['displayOption'] = $cancelParams->displayOption;
        }
        
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::CANCEL,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::CANCEL);
        return new TransactionResponse($rawResponse, UpaMessageId::CANCEL);
    }

    public function authorize($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function startTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder($transactionType, PaymentMethodType::CREDIT))
            ->withModifier(TransactionModifier::START_TRANSACTION)
            ->withAmount($amount);
    }

    public function continueTransaction(float $amount, bool $isEmv = false): TerminalAuthBuilder
    {
        $trnModifier = ($isEmv === true ?
            TransactionModifier::CONTINUE_EMV_TRANSACTION : TransactionModifier::CONTINUE_CARD_TRANSACTION);

        return (new TerminalAuthBuilder(TransactionType::CONFIRM))
            ->withModifier($trnModifier)
            ->withAmount($amount);
    }

    public function completeTransaction(): TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::CONFIRM))
            ->withModifier(TransactionModifier::COMPLETE_TRANSACTION);
    }

    public function processTransaction(float $amount, $transactionType = TransactionType::SALE) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder($transactionType, PaymentMethodType::CREDIT))
            ->withModifier(TransactionModifier::PROCESS_TRANSACTION)
            ->withAmount($amount);
    }

    /**
     * @deprecated This method is deprecated and will be removed starting with 2024. Please use method reverse()
     * @return TerminalManageBuilder
     */
    public function creditReversal()
    {
        return (new TerminalManageBuilder(TransactionType::REVERSAL, PaymentMethodType::CREDIT));
    }

    public function deletePreAuth() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::DELETE, PaymentMethodType::CREDIT))
            ->withTransactionModifier(TransactionModifier::DELETE_PRE_AUTH);
    }
    
    public function tipAdjust($tipAmount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
            ->withGratuity($tipAmount);
    }

    public function reverse() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::REVERSAL, PaymentMethodType::CREDIT));
    }

    public function tokenize(): TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(
            TransactionType::TOKENIZE, PaymentMethodType::CREDIT
        ));
    }

    public function withdrawal($amount = null) : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(
            'The selected gateway does not support this transaction type.'
        );
    }

    public function endOfDay()
    {
        return $this->batchClose();
    }

    public function getLastEOD(): IBatchCloseResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_LAST_EOD,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_LAST_EOD);

        return new TransactionResponse($rawResponse);
    }

    public function getDiagnosticReport($totalFields)
    {
        throw new UnsupportedTransactionException();
    }

    public function getLastResponse()
    {
        throw new UnsupportedTransactionException();
    }

    public function addValue($amount = null) : TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException();
    }

    public function void() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    public function lineItem(
        string $leftText,
        string $rightText = null,
        string $runningLeftText = null,
        string $runningRightText = null
    ): DeviceResponse
    {
        if (empty($leftText)) {
            throw new ApiException("Line item left text cannot be null");
        }
        $requestId = $this->upaController->requestIdProvider->getRequestId();
        $data['params']['lineItemLeft'] = $leftText;
        if (!empty($rightText)) {
            $data['params']['lineItemRight'] = $rightText;
        }
        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => UpaMessageId::LINEITEM,
                'requestId' => $requestId,
                'EcrId' => $this->ecrId ?? '1',
                'data' => [
                    'params' => $data['params'] ?? null,
                ]
            ]
        ];

        $message =  TerminalUtils::buildUpaRequest($requestMessage);
        $rawResponse = $this->upaController->send($message, UpaMessageId::LINEITEM);

        return new TransactionResponse($rawResponse);
    }

    public function reboot() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::REBOOT,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );

        $rawResponse = $this->upaController->send($message);

        return new TransactionResponse($rawResponse, UpaMessageId::REBOOT);
    }

    public function sendFile($sendFileData)
    {
        throw new UnsupportedTransactionException();
    }

    public function startDownload($deviceSettings)
    {
        throw new UnsupportedTransactionException();
    }
    
    #region Reporting Messages

    public function localDetailReport()
    {
        throw new UnsupportedTransactionException();
    }

    #endregion
    
    #region Saf
    public function sendStoreAndForward() : ISAFResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::SEND_SAF,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::SEND_SAF);

        return new UpaSAFResponse($rawResponse);
    }
    
    public function setSafMode($paramValue)
    {
        throw new UnsupportedTransactionException();
    }
    
    public function safSummaryReport($param = null)
    {
        throw new UnsupportedTransactionException();
    }
    
    public function safDelete($safIndicator)
    {
        throw new UnsupportedTransactionException();
    }

    public function getSAFReport() : TerminalReportBuilder
    {
        return new TerminalReportBuilder(TerminalReportType::GET_SAF_REPORT);
    }

    public function getBatchReport() : TerminalReportBuilder
    {
        return new TerminalReportBuilder(TerminalReportType::GET_BATCH_REPORT);
    }

    /**
     * @deprecated This method is deprecated and will be removed starting with 2024. Please use method getBatchDetails($batchId)
     *
     * @param $batchId
     * @return UpaBatchReport
     */
    public function batchReport($batchId)
    {
        $data = [];
        $data['params']['batch'] = $batchId;
        
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_BATCH_REPORT,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_BATCH_REPORT);
        return new UpaBatchReport($rawResponse, UpaMessageId::GET_BATCH_REPORT);
    }

    public function getBatchDetails(?string $batchId = null, bool $printReport = false, string|BatchReportType $reportType = null) : ITerminalReport
    {
        $builder = (new TerminalReportBuilder(TerminalReportType::GET_BATCH_DETAILS))
            ->where(UpaSearchCriteria::ECR_ID, "1");

        if (!empty($batchId)) {
            $builder->andCondition(UpaSearchCriteria::BATCH, $batchId);
        }

        if (true === $printReport) {
            $builder->andCondition(
                UpaSearchCriteria::REPORT_OUTPUT,
                implode("|", [ReportOutput::PRINT, ReportOutput::RETURN_DATA])
            );
        }

        if (!empty($reportType)) {
            $builder->andCondition(UpaSearchCriteria::REPORT_TYPE, $reportType);
        }

        return $builder->execute();
    }

    public function findBatches() : TerminalReportBuilder
    {
        return (new TerminalReportBuilder(TerminalReportType::FIND_BATCHES));
    }
    
    public function getOpenTabDetails() : TerminalReportBuilder
    {
        return (new TerminalReportBuilder(TerminalReportType::GET_OPEN_TAB_DETAILS));
    }

    public function ping(): DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::PING,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::PING);

        return new TransactionResponse($rawResponse);
    }

    public function reset(): DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::RESTART,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::RESTART);

        return new TransactionResponse($rawResponse);
    }

    /**
     * Displays a prompt for the user to remove the card. The display will be prompted only if there is a card inserted.
     * If this command is sent with no card inserted, a “Success” response will still be sent back.
     *
     * @param string|null $language
     * @return DeviceResponse
     */
    public function removeCard(string $language = ''): DeviceResponse
    {
        $data['params']['languageCode'] = strtolower($language);

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::REMOVE_CARD,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::REMOVE_CARD);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command is sent to the ECR/POS to get the versions of the different components loaded in
     * the payment terminal and the device serial number.
     *
     * @return DeviceResponse
     */
    public function getAppInfo(): DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_APP_INFO,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_APP_INFO);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command clears the data lake memory.
     *
     * @return DeviceResponse
     */
    public function clearDataLake(): DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::CLEAR_DATA_LAKE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::CLEAR_DATA_LAKE);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command sets the time zone of the device.
     *
     * @param string $timezone Time zone to which the device will be set. This is in the tz database name format.
     * @return DeviceResponse
     */
    public function setTimeZone(string $timezone) : DeviceResponse
    {
        $data = [];
        $data['params']['timeZone'] = $timezone;

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::SET_TIME_ZONE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::SET_TIME_ZONE);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command is used to pull the full parameters or list of parameters that are currently set
     * in the Unified Payments Application.
     *
     * @param array $params
     * @return DeviceResponse
     */
    public function getParam(array $params = []): DeviceResponse
    {
        $data = [];
        $data['params']['configuration'] = $params ?? ['all'];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_PARAM,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_PARAM);

        return new TransactionResponse($rawResponse);
    }

    public function communicationCheck() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::COMMUNICATION_CHECK,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::COMMUNICATION_CHECK);

        return new TransactionResponse($rawResponse);
    }

    public function logon() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::LOGON,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::LOGON);

        return new TransactionResponse($rawResponse);
    }
    #endregion

    public function getSignatureFile(SignatureData $signatureData = null) : ISignatureResponse
    {
        $data['params'] = [
            'prompt1' => $signatureData->prompts->prompt1 ?? null,
            'prompt2' => $signatureData->prompts->prompt2 ?? null,
            'displayOption' => (string) $signatureData->displayOption ?? null,
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_SIGNATURE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_SIGNATURE);

        return new SignatureResponse($rawResponse);
    }

    public function registerPOS(POSData $POSData) : DeviceResponse
    {
        if (!empty($POSData)) {
            $data['params'] = [
                'appName' => $POSData->appName,
                'launchOrder' => (string) ($POSData->launchOrder ?? null),
                'remove' => isset($POSData->remove) ? json_encode($POSData->remove) : $POSData->remove,
                'silent' => (string) ($POSData->silent ?? null)
            ];
        }

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => UpaMessageId::REGISTER_POS,
                'requestId' => $this->upaController->requestIdProvider->getRequestId(),
                'EcrId' => $this->upaController->deviceInterface->ecrId ?? "1",
                'data' => $data ?? []
            ]
        ];

        $message = TerminalUtils::buildUpaRequest(ArrayUtils::array_remove_empty($requestMessage));

        $rawResponse = $this->upaController->send($message, UpaMessageId::REGISTER_POS);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command sets the Broadcast configurations in the Unified Payments application.
     * If it is enabled, Broadcast messages will be received by the POS.
     *
     * @param bool $enable
     * @return DeviceResponse
     */
    public function broadcastConfiguration(bool $enable) : DeviceResponse
    {
        $data['params'] = [
            'enable' => (string) ((int) $enable),
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::BROADCAST_CONFIGURATION,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $data
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::BROADCAST_CONFIGURATION);

        return new TransactionResponse($rawResponse);
    }

    public function setDebugLevel(array $debugLevels,string $logOutput = null) : DeviceResponse
    {
        $debugLevel = '';
        array_walk($debugLevels, function ($v) use (&$debugLevel) {
            $debugLevel .= DebugLevel::getKey($v) . '|';
        });
        $data['params'] = [
            'debugLevel' => rtrim($debugLevel, '|'),
            'logToConsole' => $logOutput
        ];

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => UpaMessageId::SET_DEBUG_LEVEL,
                'requestId' => $this->upaController->requestIdProvider->getRequestId(),
                'EcrId' => $this->ecrId ?? "13",
                'data' => $data
            ]
        ];
        $message = TerminalUtils::buildUpaRequest(ArrayUtils::array_remove_empty($requestMessage));
        $rawResponse = $this->upaController->send($message, UpaMessageId::SET_DEBUG_LEVEL);

        return new TransactionResponse($rawResponse);
    }

    public function getDebugLevel() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_DEBUG_LEVEL,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_DEBUG_LEVEL);

        return new TransactionResponse($rawResponse);
    }

    public function getDebugInfo(string $logDirectory, string $fileIndicator = null) : DeviceResponse
    {
        $data['params'] = [
            'logFile' => $fileIndicator,
        ];
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_DEBUG_INFO,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_DEBUG_INFO);

        $parsedResponse = new TransactionResponse($rawResponse);
        if ($parsedResponse->debugFileContents) {
            $logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
            if( !is_dir($logDirectory) ) {
                mkdir($logDirectory, 0777, true);
            }
            $logFilePath = $logDirectory . DIRECTORY_SEPARATOR . 'Debuglog'.($fileIndicator ?? '').'.log';
            file_put_contents(
                $logFilePath,
                $parsedResponse->debugFileContents
            );
        }

        return $parsedResponse;
    }

    public function returnToIdle(): DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::RETURN_TO_IDLE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::RETURN_TO_IDLE);

        return new TransactionResponse($rawResponse);
    }

    public function loadUDData(UDData $screen) : IDeviceScreen
    {
        $data['params'] = [
            'fileType' => $screen->fileType,
            'slotNum' => $screen->slotNum,
            'file' => $screen->file
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::LOAD_UD_SCREEN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::LOAD_UD_SCREEN);

        return new UDScreenResponse($rawResponse);
    }

    public function removeUDData(UDData $screen) : IDeviceScreen
    {
        $data['params'] = [
            'fileType' => $screen->fileType,
            'slotNum' => $screen->slotNum
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::REMOVE_UD_SCREEN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::REMOVE_UD_SCREEN);

        return new UDScreenResponse($rawResponse);
    }

    public function executeUDData(UDData $screen) : IDeviceScreen
    {
        $data['params'] = [
            'fileType' => $screen->fileType,
            'slotNum' => $screen->slotNum,
            'displayOption' => $screen->displayOption ?? null
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::EXECUTE_UD_SCREEN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::EXECUTE_UD_SCREEN);

        return new UDScreenResponse($rawResponse);
    }

    /**
     * Enables users to inject UDData without UDS.
     *
     * @param UDData $screen
     * @return IDeviceScreen
     * @throws MessageException
     */
    public function injectUDData(UDData $screen) : IDeviceScreen
    {
        if (empty($screen->fileType) || empty($screen->fileName) || empty($screen->localFile)) {
            throw new MessageException(
                "Mandatory fields missing! Please check the properties: fileType, fileName, fileContent"
            );
        }
        if (mime_content_type($screen->localFile) != 'text/html') {
            $encodedFile =  base64_encode(file_get_contents($screen->localFile));
            $fileContent = 'data:' . mime_content_type($screen->localFile) . ';base64,' . $encodedFile;
        } else {
            $fileContent = trim(preg_replace('/\s+/', ' ', file_get_contents($screen->localFile)));
        }

        $data['params'] = [
            'fileType' => $screen->fileType,
            'fileName' => $screen->fileName,
            'content' => $fileContent
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::INJECT_UD_SCREEN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($data)
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::INJECT_UD_SCREEN);

        return new UDScreenResponse($rawResponse);
    }

    /**
     * The Scan command supports the scan feature of the PIA that reads Gift QR codes. The scan feature is designed to
     * read other 2D/3D barcodes or other QR codes other than Gift QR codes.
     *
     * @param ScanData $data
     * @return DeviceResponse
     */
    public function scan(ScanData $data) : DeviceResponse
    {
        $params['params'] = [
            'header' => $data->header ?? null,
            'prompt1' => $data->prompts->prompt1 ?? null,
            'prompt2' => $data->prompts->prompt2 ?? null,
            'displayOption' => $data->displayOption ?? null,
            'timeOut' => $data->timeout ?? null
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::SCAN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($params)
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::SCAN);

        return new TransactionResponse($rawResponse);
    }

    public function print(PrintData $data): DeviceResponse
    {
        if (empty($data->filePath) || empty($data->line1)) {
            throw new MessageException("Mandatory parameters missing!");
        }
        if (!file_exists($data->filePath)) {
            throw new MessageException("File {$data->filePath} not found!");
        }
        $imageData =  base64_encode(file_get_contents($data->filePath));
        $src = 'data:' . mime_content_type($data->filePath) . ';base64,' . $imageData;

        $params['params'] = [
            'content' => $src,
            'line1' => $data->line1,
            'line2' => $data->line2 ?? null,
            'displayOption' => $data->displayOption ?? null,
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::PRINT,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($params)
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::PRINT);

        return new TransactionResponse($rawResponse);
    }

    public function getDeviceConfig(string|DeviceConfigType $configType) : DeviceResponse
    {
        $params['params'] = [
            'configType' => $configType
        ];

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_CONFIG_CONTENTS,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            ArrayUtils::array_remove_empty($params)
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_CONFIG_CONTENTS);

        return new TerminalSetupResponse($rawResponse);
    }

    public function enterPIN(PromptMessages $promptMessages, bool $canBypass, string $accountNumber) : DeviceResponse
    {
        $params =[
            'params' => [
                'prompt1' => $promptMessages->prompt1,
                'prompt2' => $promptMessages->prompt2,
                'prompt3' => $promptMessages->prompt3
            ],
            'terminal' => [
                'canBypass' => $canBypass === true ? 'Y' : 'N'
            ],
            'transaction' => [
                'accountNumber' => $accountNumber
            ]
        ];
        $this->validateMandatoryParams($params, ['prompt1', 'canBypass', 'accountNumber']);

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::ENTER_PIN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $params
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::ENTER_PIN);

        return new TransactionResponse($rawResponse);
    }

    public function prompt(string $promptType, PromptData $promptData): DeviceResponse
    {
        $params['params'] = [
            'prompt1' => $promptData->prompts->prompt1,
            'timeout' => $promptData->timeout
        ];
        if (!empty($promptData->buttons)) {
            foreach ($promptData->buttons->all() as $index => $button) {
                $key = $index+1;
                $params['params']["button{$key}"] = [
                    'text' => $button->text,
                    'color' => $button->color
                ];
            }
        }
        $mandatoryParams = [];
        switch ($promptType) {
            case PromptType::OPTIONS:
                $params['params'] += [
                    'prompt2' => $promptData->prompts->prompt2,
                    'prompt3' => $promptData->prompts->prompt3,
                ];
                $mandatoryParams = ['button1'];
                $messageType = UpaMessageId::PROMPT_WITH_OPTIONS;
                break;
            case PromptType::MENU:
                $params['params'] += [
                    'menu' => $promptData->menu
                ];
                $mandatoryParams = ['prompt1', 'menu', 'button1'];
                $messageType = UpaMessageId::PROMPT_MENU;
                break;
            default:
                throw new MessageException('Unsupported prompt type!');
        }

        $this->validateMandatoryParams($params, $mandatoryParams);

        $message = TerminalUtils::buildUPAMessage(
            $messageType,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $params
        );
        $rawResponse = $this->upaController->send($message, $messageType);

        return new TransactionResponse($rawResponse);
    }

    public function updateTaxInfo(?float $amount = null): TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT))
            ->withTransactionModifier(TransactionModifier::UPDATE_TAX_DETAILS)
            ->withTaxAmount($amount);
    }

    public function updateLodgingDetails(float $amount): TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT))
            ->withTransactionModifier(TransactionModifier::UPDATE_LODGING_DETAILS)
            ->withAmount($amount);
    }

    public function getGenericEntry(GenericData $genericData): DeviceResponse
    {
        $request['params'] = [
            'prompt1' => $genericData->prompts->prompt1,
            'prompt2' => $genericData->prompts->prompt2,
            'prompt3' => $genericData->prompts->prompt3,
            'button1' => $genericData->textButton1,
            'button2' => $genericData->textButton2,
            'timeout' => $genericData->timeout,
            'entryFormat' => implode("|", $genericData->entryFormat),
            'minLen' => $genericData->entryMinLen,
            'maxLen' => $genericData->entryMaxLen,
            'alignment' => $genericData->alignment,
        ];
        $this->validateMandatoryParams($request, ['button1', 'button2', 'entryFormat', 'maxLen']);

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GENERAL_ENTRY,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $request
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::GENERAL_ENTRY);

        return new TransactionResponse($rawResponse);
    }

    public function displayMessage(MessageLines $messageLines) :DeviceResponse
    {
        $request['params'] = [
            'line1' => $messageLines->line1,
            'line2' => $messageLines->line2,
            'line3' => $messageLines->line3,
            'line4' => $messageLines->line4,
            'line5' => $messageLines->line5,
            'timeout' => $messageLines->timeout
        ];
        $this->validateMandatoryParams($request, ['line1']);

        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::DISPLAY_MESSAGE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $request
        );

        $rawResponse = $this->upaController->send($message, UpaMessageId::DISPLAY_MESSAGE);

        return new TransactionResponse($rawResponse);
    }

    /**
     * @param string|DisplayOption $option
     * @return DeviceResponse
     */
    public function returnDefaultScreen(string $option) : DeviceResponse
    {
        $params['params'] = [
            'displayOption' => $option
        ];
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::RETURN_DEFAULT_SCREEN,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId,
            $params
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::RETURN_DEFAULT_SCREEN);

        return new TransactionResponse($rawResponse);
    }

    /**
     * This command gets the encryption type currently being used by the application to encrypt the account data and
     * other sensitive card data.
     *
     * @return DeviceResponse
     */
    public function getEncryptionType() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_ENCRYPTION_TYPE,
            $this->upaController->requestIdProvider->getRequestId(),
            $this->ecrId
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_ENCRYPTION_TYPE);

        return new TransactionResponse($rawResponse);
    }

    private function validateMandatoryParams(&$request,array $mandatoryParams)
    {
        $this->validations->setMandatoryParams($mandatoryParams);
        $request = ArrayUtils::array_remove_empty($request);
        if (($missingParams = $this->validations->validate($request)) !== true) {
            throw new ArgumentException(sprintf('Mandatory params missing: %s !', $missingParams));
        }
    }
}
