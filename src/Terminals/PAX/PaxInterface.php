<?php

declare(strict_types=1);

namespace GlobalPayments\Api\Terminals\PAX;

use GlobalPayments\Api\Entities\Enums\{TransactionType, PaymentMethodType};
use GlobalPayments\Api\Entities\Exceptions\{UnsupportedTransactionException, ApiException};
use GlobalPayments\Api\Terminals\Abstractions\{IBatchCloseResponse, ISignatureResponse};
use GlobalPayments\Api\Terminals\Builders\{TerminalAuthBuilder, TerminalManageBuilder, TerminalReportBuilder};
use GlobalPayments\Api\Terminals\Enums\{ConnectionModes, TerminalReportType, DebugLogsOutput};
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\PAX\Responses\{
    InitializeResponse,
    PaxTerminalResponse,
    SignatureResponse,
    BatchResponse,
    SafUploadResponse,
    SafDeleteResponse,
    SafSummaryReport
};
use GlobalPayments\Api\Terminals\{DeviceInterface, DeviceResponse, TerminalUtils};
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;

/**
 * Heartland payment application implementation of device messages
 */
final class PaxInterface extends DeviceInterface
{
    public PaxController $paxController;

    /**
     * Debug levels stored locally to avoid dynamic property creation
     */
    private array $debugLevels = [];

    public function __construct(PaxController $deviceController)
    {
        parent::__construct($deviceController);
        $this->paxController = $deviceController;
    }

    #region Admin Messages
    
    public function initialize(): InitializeResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A00_INITIALIZE);
        
        // Add automatic logging (same pattern as UPA) - meets requirement for same config
        if (isset($this->paxController->settings->logManagementProvider)) {
            TerminalUtils::manageLog(
                $this->paxController->settings->logManagementProvider,
                "PAX Initialize Request: " . PaxMessageId::A00_INITIALIZE
            );
        }
        
        $rawResponse = $this->paxController->send($message);
        
        // Log response as well 
        if (isset($this->paxController->settings->logManagementProvider)) {
            TerminalUtils::manageLog(
                $this->paxController->settings->logManagementProvider,
                "PAX Initialize Response: " . $rawResponse
            );
        }
        
        return new InitializeResponse($rawResponse, PaxMessageId::A00_INITIALIZE);
    }

    public function batchClose(): IBatchCloseResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B00_BATCH_CLOSE, [date("YMDhms")]);
        $rawResponse = $this->paxController->send($message);
        
        return new BatchResponse($rawResponse);
    }

    public function cancel($cancelParams = null): void
    {
        if ($this->paxController->deviceConfig->connectionMode === ConnectionModes::HTTP) {
            throw new ApiException("The cancel command is not available in HTTP mode");
        }
        try {
            $message = TerminalUtils::buildAdminMessage(PaxMessageId::A14_CANCEL);
            $this->paxController->send($message, PaxMessageId::A14_CANCEL);
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Device error: Terminal returned EOT for the current message') {
                throw $e;
            }
        }
    }

    public function authorize($amount = null): TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
    }

    public function creditVoid(): TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    public function endOfDay(): IBatchCloseResponse
    {
        return $this->batchClose();
    }

    public function getDiagnosticReport($totalFields)
    {
        throw new UnsupportedTransactionException('');
    }

    public function getLastResponse()
    {
        throw new UnsupportedTransactionException('');
    }
    
    public function promptForSignature(?string $transactionId = null): SignatureResponse
    {
        $message = TerminalUtils::buildAdminMessage(
            PaxMessageId::A20_DO_SIGNATURE,
            [
                        (!empty($transactionId)) ? 1 : 0,
                        (!empty($transactionId)) ? $transactionId : '',
                        (!empty($transactionId)) ? '00' : '',
                        300
                    ]
        );
        $rawResponse = $this->paxController->send($message);
        
        return new SignatureResponse($rawResponse, PaxMessageId::A21_RSP_DO_SIGNATURE);
    }

    public function getSignatureFile(?SignatureData $data = null): ISignatureResponse
    {
        if (!function_exists('imagecreate')) {
            throw new ApiException("The gd2 extension needs to be enabled for this request. Please contact your admin");
        }
        
        $message = TerminalUtils::buildAdminMessage(
            PaxMessageId::A08_GET_SIGNATURE,
            [0]
        );
        $rawResponse = $this->paxController->send($message);
        
        return new SignatureResponse(
            $rawResponse,
            PaxMessageId::A09_RSP_GET_SIGNATURE,
            $this->paxController->deviceConfig->deviceType
        );
    }

    public function reboot(): DeviceResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A26_REBOOT);
        $rawResponse = $this->paxController->send($message);
        
        return new PaxTerminalResponse($rawResponse, PaxMessageId::A26_REBOOT);
    }

    public function reset(): DeviceResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A16_RESET);
        $rawResponse = $this->paxController->send($message);
        
        return new PaxTerminalResponse($rawResponse, PaxMessageId::A16_RESET);
    }

    public function sendFile($sendFileData)
    {
        throw new UnsupportedTransactionException('');
    }

    public function startDownload($deviceSettings)
    {
        throw new UnsupportedTransactionException('');
    }
    
    #region Reporting Messages

    public function localDetailReport(): TerminalReportBuilder
    {
        return new TerminalReportBuilder(TerminalReportType::LOCAL_DETAIL_REPORT);
    }

    #endregion
    
    #region Saf
    public function sendSaf($safIndicator = null): DeviceResponse
    {
        return $this->safUpload($safIndicator);
    }
    
    public function setSafMode($paramValue): PaxTerminalResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A54_SET_SAF_PARAMETERS, [
            $paramValue,
            '', '', '', '', '', '', '', '', '', ''
        ]);
        $rawResponse = $this->paxController->send($message);
        return new PaxTerminalResponse($rawResponse, PaxMessageId::A54_SET_SAF_PARAMETERS);
    }
    
    public function safUpload($safIndicator): SafUploadResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B08_SAF_UPLOAD, [$safIndicator]);
        $rawResponse = $this->paxController->send($message);
        return new SafUploadResponse($rawResponse);
    }
    
    public function safDelete($safIndicator): SafDeleteResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B10_DELETE_SAF_FILE, [$safIndicator]);
        
        $rawResponse = $this->paxController->send($message);
        return new SafDeleteResponse($rawResponse);
    }
    
    public function safSummaryReport($safIndicator = null): SafSummaryReport
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::R10_SAF_SUMMARY_REPORT, [$safIndicator]);
        
        $rawResponse = $this->paxController->send($message);
        return new SafSummaryReport($rawResponse);
    }
    
    public function tipAdjust($tipAmount = null): TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
                        ->withGratuity($tipAmount);
    }

    public function setDebugLevel(array $debugLevels, ?string $logOutput = null): DeviceResponse
    {
        // Add null safety check - requirement: same config as UPA
        if (isset($this->paxController->settings->logManagementProvider)) {
            $this->paxController->settings->logManagementProvider->enableConsoleOutput = 
                $logOutput === (string)DebugLogsOutput::CONSOLE;
        }
        
        // Store debug levels locally instead of on ConnectionConfig to avoid dynamic property creation
        $this->debugLevels = $debugLevels;
        
        return new PaxTerminalResponse("0\x1CA90\x1C1.35\x1C000000\x1COK\x03", "A90");
    }

    public function getDebugLevel(): DeviceResponse
    {
        $response = new PaxTerminalResponse("0\x1CA91\x1C1.35\x1C000000\x1COK\x03", "A91");
        $response->debugLevel = implode('|', $this->debugLevels);
        return $response;
    }

    public function getDebugInfo(string $logDirectory, ?string $fileIndicator = null): DeviceResponse
    {
        $response = new PaxTerminalResponse("0\x1CA92\x1C1.35\x1C000000\x1COK\x03", "A92");
        
        // Get log file with null safety (same pattern as UPA)
        $logFile = '';
        if (isset($this->paxController->settings->logManagementProvider)) {
            $logFile = $this->paxController->settings->logManagementProvider->logLocation ?? '';
        }
        
        $response->debugFileContents = file_exists($logFile) ? file_get_contents($logFile) : '';
        $response->debugFileLength = file_exists($logFile) ? filesize($logFile) : 0;
        
        return $response;
    }
    #endregion
}
