<?php

namespace GlobalPayments\Api\Terminals\PAX;

use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Abstractions\ISignatureResponse;
use GlobalPayments\Api\Terminals\DeviceInterface;
use GlobalPayments\Api\Terminals\DeviceResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\PAX\Responses\InitializeResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxTerminalResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\SignatureResponse;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\Enums\TerminalReportType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\PAX\Responses\BatchResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\SafUploadResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\SafDeleteResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\SafSummaryReport;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;

/**
 * Heartland payment application implementation of device messages
 */
class PaxInterface extends DeviceInterface
{
    /*
     * PaxController object
     */

    public $paxController;

    public function __construct(PaxController $deviceController)
    {
        parent::__construct($deviceController);
        $this->paxController = $deviceController;
    }

    #region Admin Messages
    
    public function initialize()
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A00_INITIALIZE);
        $rawResponse = $this->paxController->send($message);
        
        return new InitializeResponse($rawResponse, PaxMessageId::A00_INITIALIZE);
    }

    public function batchClose() : IBatchCloseResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B00_BATCH_CLOSE, [date("YMDhms")]);
        $rawResponse = $this->paxController->send($message);
        
        return new BatchResponse($rawResponse);
    }

    public function cancel($cancelParams = null)
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

    public function authorize($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
    }

    public function creditVoid()
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    public function endOfDay()
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
    
    public function promptForSignature(string $transactionId = null)
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

    public function getSignatureFile(SignatureData $data = null) : ISignatureResponse
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

    public function reboot() : DeviceResponse
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A26_REBOOT);
        $rawResponse = $this->paxController->send($message);
        
        return new PaxTerminalResponse($rawResponse, PaxMessageId::A26_REBOOT);
    }

    public function reset() : DeviceResponse
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

    public function localDetailReport()
    {
        return new TerminalReportBuilder(TerminalReportType::LOCAL_DETAIL_REPORT);
    }

    #endregion
    
    #region Saf
    public function sendSaf($safIndicator = null) : DeviceResponse
    {
        return $this->safUpload($safIndicator);
    }
    
    public function setSafMode($paramValue)
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::A54_SET_SAF_PARAMETERS, [
            $paramValue,
            '', '', '', '', '', '', '', '', '', ''
        ]);
        $rawResponse = $this->paxController->send($message);
        return new PaxTerminalResponse($rawResponse, PaxMessageId::A54_SET_SAF_PARAMETERS);
    }
    
    public function safUpload($safIndicator)
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B08_SAF_UPLOAD, [$safIndicator]);
        $rawResponse = $this->paxController->send($message);
        return new SafUploadResponse($rawResponse);
    }
    
    public function safDelete($safIndicator)
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::B10_DELETE_SAF_FILE, [$safIndicator]);
        
        $rawResponse = $this->paxController->send($message);
        return new SafDeleteResponse($rawResponse);
    }
    
    public function safSummaryReport($safIndicator = null)
    {
        $message = TerminalUtils::buildAdminMessage(PaxMessageId::R10_SAF_SUMMARY_REPORT, [$safIndicator]);
        
        $rawResponse = $this->paxController->send($message);
        return new SafSummaryReport($rawResponse);
    }
    
    public function tipAdjust($tipAmount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
                        ->withGratuity($tipAmount);
    }

    #endregion
}
