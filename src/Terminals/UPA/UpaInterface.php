<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType, TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\{
    ApiException, UnsupportedTransactionException
};
use GlobalPayments\Api\Terminals\{
    DeviceInterface, TerminalUtils, DeviceResponse
};
use GlobalPayments\Api\Terminals\Builders\{
    TerminalAuthBuilder, TerminalManageBuilder
};
use GlobalPayments\Api\Terminals\UPA\Builders\UpaTerminalManageBuilder;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\UPA\Responses\{
    UpaBatchReport, UpaDeviceResponse, UpaReportHandler
};

/**
 * Heartland payment application implementation of device messages
 */
class UpaInterface extends DeviceInterface
{
    /*
     * UpaController object
     */

    public $upaController;

    public function __construct(UpaController $deviceController)
    {
        $this->upaController = $deviceController;
    }

    #region Admin Messages

    public function batchClose()
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::EOD,
            $this->upaController->requestIdProvider->getRequestId()
        );
                
        $rawResponse = $this->upaController->send($message, UpaMessageId::EOD);
        return new UpaDeviceResponse($rawResponse, UpaMessageId::EOD);
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
            $data
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::CANCEL);
        return new UpaDeviceResponse($rawResponse, UpaMessageId::CANCEL);
    }

    public function authorize($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function creditReversal()
    {
        return (new UpaTerminalManageBuilder(TransactionType::REVERSAL, PaymentMethodType::CREDIT));
    }
    
    public function tipAdjust($tipAmount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
            ->withGratuity($tipAmount);
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
        return (new UpaTerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
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
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::LINEITEM,
            $this->upaController->requestIdProvider->getRequestId(),
            $data
        );
        $rawResponse = $this->upaController->send($message, UpaMessageId::LINEITEM);

        return new UpaDeviceResponse($rawResponse, UpaMessageId::LINEITEM);
    }

    public function reboot() : DeviceResponse
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::REBOOT,
            $this->upaController->requestIdProvider->getRequestId()
        );

        $rawResponse = $this->upaController->send($message);

        return new UpaDeviceResponse($rawResponse, UpaMessageId::REBOOT);
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
    public function sendSaf($safIndicator = null) : DeviceResponse
    {
        throw new UnsupportedTransactionException();
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
    
    public function batchReport($batchId)
    {
        $data = [];
        $data['params']['batch'] = $batchId;
        
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_BATCH_REPORT,
            $this->upaController->requestIdProvider->getRequestId(),
            $data
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_BATCH_REPORT);
        return new UpaBatchReport($rawResponse, UpaMessageId::GET_BATCH_REPORT);
    }
    
    public function getOpenTabDetails()
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::GET_OPEN_TAB_DETAILS,
            $this->upaController->requestIdProvider->getRequestId()
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::GET_OPEN_TAB_DETAILS);
        return new UpaReportHandler($rawResponse, UpaMessageId::GET_OPEN_TAB_DETAILS);
    }
    
    
    #endregion
}
