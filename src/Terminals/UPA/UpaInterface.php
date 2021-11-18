<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceInterface;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\UPA\Responses\UpaDeviceResponse;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\UPA\Builders\UpaTerminalManageBuilder;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Terminals\UPA\Responses\UpaBatchReport;

/**
 * Heartland payment application implementation of device messages
 */
class UpaInterface implements IDeviceInterface
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
    
    public function initialize()
    {
        throw new UnsupportedTransactionException('');
    }

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

    public function closeLane()
    {
        throw new UnsupportedTransactionException('');
    }

    public function creditAuth($amount = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function creditCapture($amount = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function creditRefund($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::CREDIT))
        ->withAmount($amount);
    }
    
    public function creditReversal()
    {
        return (new UpaTerminalManageBuilder(TransactionType::REVERSAL, PaymentMethodType::CREDIT));
    }

    public function creditSale($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
    }

    public function creditVerify()
    {
        return (new TerminalAuthBuilder(TransactionType::VERIFY, PaymentMethodType::CREDIT));
    }

    public function creditVoid()
    {
        return (new UpaTerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }
    
    public function creditTipAdjust($tipAmount = null)
    {
        return (new UpaTerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
            ->withGratuity($tipAmount);
    }
    
    public function debitRefund($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::DEBIT))
        ->withAmount($amount);
    }

    public function debitSale($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::DEBIT))
        ->withAmount($amount);
    }

    public function disableHostResponseBeep()
    {
        throw new UnsupportedTransactionException('');
    }

    public function ebtBalance()
    {
        $message = TerminalUtils::buildUPAMessage(
            UpaMessageId::BALANCE_INQUIRY,
            $this->upaController->requestIdProvider->getRequestId()
        );
        
        $rawResponse = $this->upaController->send($message, UpaMessageId::BALANCE_INQUIRY);
        return new UpaDeviceResponse($rawResponse, UpaMessageId::BALANCE_INQUIRY);
    }

    public function ebtPurchase($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::EBT))
        ->withAmount($amount);
    }

    public function ebtRefund($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::EBT))
        ->withAmount($amount);
    }

    public function ebtWithdrawl($amount = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function eod()
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
    
    public function promptForSignature($transactionId = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function getSignatureFile()
    {
        throw new UnsupportedTransactionException('');
    }

    public function giftAddValue($amount = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function giftBalance()
    {
        throw new UnsupportedTransactionException('');
    }

    public function giftSale($amount = null)
    {
        throw new UnsupportedTransactionException('');
    }

    public function giftVoid()
    {
        throw new UnsupportedTransactionException('');
    }

    public function lineItem($lineItemDetails)
    {
        foreach ($lineItemDetails as $lineItem) {
            if (empty($lineItem->lineItemLeft)) {
                throw new ApiException("Line item left text cannot be null");
            }
    
            $data = [];
            
            $data['params']['lineItemLeft'] = $lineItem->lineItemLeft;
            if (!empty($lineItem->lineItemRight)) {
                $data['params']['lineItemRight'] = $lineItem->lineItemRight;
            }
            
            $message = TerminalUtils::buildUPAMessage(
                UpaMessageId::LINEITEM,
                $this->upaController->requestIdProvider->getRequestId(),
                $data
            );
    
            $rawResponse = $this->upaController->send($message, UpaMessageId::LINEITEM);
        }
        return new UpaDeviceResponse($rawResponse, UpaMessageId::LINEITEM);
    }

    public function openLane()
    {
        throw new UnsupportedTransactionException('');
    }

    public function reboot()
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
        throw new UnsupportedTransactionException('');
    }

    public function startCard($paymentMethodType = null)
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
        throw new UnsupportedTransactionException('');
    }

    #endregion
    
    #region Saf
    public function sendSaf($safIndicator = null)
    {
        throw new UnsupportedTransactionException('');
    }
    
    public function setSafMode($paramValue)
    {
        throw new UnsupportedTransactionException('');
    }
    
    public function safSummaryReport($param = null)
    {
        throw new UnsupportedTransactionException('');
    }
    
    public function safDelete($safIndicator)
    {
        throw new UnsupportedTransactionException('');
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
        
    public function reset()
    {
        throw new UnsupportedTransactionException('');
    }
    
    #endregion
}
