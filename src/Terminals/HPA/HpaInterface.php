<?php

namespace GlobalPayments\Api\Terminals\HPA;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceInterface;
use GlobalPayments\Api\Terminals\HPA\Entities\Enums\HpaMessageId;
use GlobalPayments\Api\Terminals\HPA\HpaController;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;

/**
 * Heartland payment application implementation of device messages
 */
class HpaInterface implements IDeviceInterface
{
    /*
     * HpaController object
     */

    public $hpaController;

    public function __construct(HpaController $deviceController)
    {
        $this->hpaController = $deviceController;
    }

    #region Admin Messages

    /*
     * GetAppInfoReport - Admin mode message - Get HeartSIP Application Information Report
     */

    public function initialize()
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>GetAppInfoReport</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>",
            HpaMessageId::GET_INFO_REPORT
        );
    }

    /*
     * LaneOpen - Admin mode message - Go to Lane Open State
     */

    public function openLane()
    {
        return $this->hpaController->send(
            "<SIP>"
            . "<Version>1.0</Version>"
            . "<ECRId>1004</ECRId>"
            . "<Request>LaneOpen</Request>"
            . "<RequestId>%s</RequestId>"
            . "</SIP>"
        );
    }

    /*
     * LaneClose - Admin mode message - Go to Lane Close State
     */

    public function closeLane()
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>LaneClose</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>"
        );
    }

    /*
     * Reset - Admin mode message - Transition SIP to idle state
     */

    public function cancel()
    {
        return $this->reset();
    }

    /*
     * Reboot - Admin mode message - Reboot the SIP device
     */

    public function reboot()
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>Reboot</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>"
        );
    }

    /*
     * Reset - Admin mode message - Transition SIP to idle state
     */

    public function reset()
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>Reset</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>"
        );
    }
    
    public function lineItem($lineItemDetails)
    {
        if (empty($lineItemDetails->leftText)) {
            throw new BuilderException("Line item left text cannot be null");
        }
        $message = "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>LineItem</Request>"
                . "<RequestId>%s</RequestId>"
                ."<LineItemTextLeft>{$lineItemDetails->leftText}</LineItemTextLeft>";
        
        if (!empty($lineItemDetails->rightText)) {
            $message .= sprintf("<LineItemTextRight>%s</LineItemTextRight>", $lineItemDetails->rightText);
        }
        if (!empty($lineItemDetails->runningLeftText)) {
            $message .= sprintf(
                "<LineItemRunningTextLeft>%s</LineItemRunningTextLeft>",
                $lineItemDetails->runningLeftText
            );
        }
        if (!empty($lineItemDetails->runningRightText)) {
            $message .= sprintf(
                "<LineItemRunningTextRight>%s</LineItemRunningTextRight>",
                $lineItemDetails->runningRightText
            );
        }
        
        $message .= "</SIP>";
        return $this->hpaController->send($message);
    }
    
    /*
     * StartCard - Admin mode message - Initiate card acquisition prior to a financial transaction.
     * The intent is to perform card acquisition while the clerk is ringing up the items
     */

    public function startCard($paymentMethodType = null)
    {
        $message = "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>StartCard</Request>"
                . "<RequestId>%s</RequestId>";
        
        if ($paymentMethodType !== null) {
            $cardGroup = $this->hpaController->manageCardGroup($paymentMethodType);
            $message .= "<CardGroup>$cardGroup</CardGroup>";
        }
        
        $message .= "</SIP>";
        
        return $this->hpaController->send($message);
    }

    #endregion
    
    #credit

    public function batchClose()
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>EOD</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>",
            HpaMessageId::EOD
        );
    }
    
    public function eod()
    {
        return $this->batchClose();
    }

    public function creditAuth($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
    }

    public function creditCapture($amount = null)
    {
        return (new TerminalManageBuilder(TransactionType::CAPTURE, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
    }

    public function creditRefund($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::CREDIT))
                        ->withAmount($amount);
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
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    #end credit

    public function debitSale($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::DEBIT))
                        ->withAmount($amount);
    }

    public function debitRefund($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::DEBIT))
                        ->withAmount($amount);
    }
    
    public function disableHostResponseBeep()
    {
    }

    public function getSignatureFile()
    {
    }

    public function ebtBalance()
    {
        return (new TerminalAuthBuilder(TransactionType::BALANCE, PaymentMethodType::EBT));
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
        throw new UnsupportedTransactionException(
            'The selected gateway does not support this transaction type.'
        );
    }
    
    public function startDownload($deviceSettings)
    {
        $startDownloadRequest = sprintf(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>Download</Request>"
                . "<RequestId>%s</RequestId>"
                . "<HUDSURL>%s</HUDSURL>"
                . "<HUDSPORT>%s</HUDSPORT>"
                . "<TerminalID>%s</TerminalID>"
                . "<ApplicationID>%s</ApplicationID>"
                . "<DownloadType>%s</DownloadType>"
                . "<DownloadTime>%s</DownloadTime>",
            "%s",
            $deviceSettings->hudsUrl,
            $deviceSettings->hudsPort,
            $deviceSettings->terminalId,
            $deviceSettings->applicationId,
            $deviceSettings->downloadType,
            $deviceSettings->downloadTime
        );
        
        $startDownloadRequest .= "</SIP>";
        return $this->hpaController->send($startDownloadRequest);
    }
    
    #Gift Region
    public function giftAddValue($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::ADD_VALUE, PaymentMethodType::GIFT))
                        ->withAmount($amount)
                        ->withCurrency(CurrencyType::CURRENCY);
    }

    public function giftBalance()
    {
        return (new TerminalAuthBuilder(TransactionType::BALANCE, PaymentMethodType::GIFT))
                        ->withCurrency(CurrencyType::CURRENCY);
    }

    public function giftSale($amount = null)
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::GIFT))
                        ->withAmount($amount)
                        ->withCurrency(CurrencyType::CURRENCY);
    }

    public function giftVoid()
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::GIFT));
    }
    
    public function setSafMode($parameterValue)
    {
        return $this->hpaController->send(
            sprintf(
                "<SIP>"
                    . "<Version>1.0</Version>"
                    . "<ECRId>1004</ECRId>"
                    . "<Request>SetParameter</Request>"
                    . "<RequestId>%s</RequestId>"
                    . "<FieldCount>1</FieldCount>"
                    . "<Key>STORMD</Key>"
                    . "<Value>%s</Value>"
                . "</SIP>",
                '%s',
                $parameterValue
            )
        );
    }
    
    public function sendSaf($safIndicator = null)
    {
        return $this->hpaController->send(
            "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>SendSAF</Request>"
                . "<RequestId>%s</RequestId>"
                . "</SIP>",
            HpaMessageId::SENDSAF
        );
    }
    
    public function safDelete($safIndicator)
    {
        throw new UnsupportedTransactionException(
            'The selected gateway does not support this transaction type.'
        );
    }

    public function sendFile($sendFileData)
    {
        return $this->hpaController->sendFile($sendFileData);
    }
  
    public function getDiagnosticReport($totalFields)
    {
        return $this->hpaController->send(
            sprintf(
                "<SIP>"
                    . "<Version>1.0</Version>"
                    . "<ECRId>1004</ECRId>"
                    . "<Request>GetDiagnosticReport</Request>"
                    . "<RequestId>%s</RequestId>"
                    . "<FieldCount>%s</FieldCount>"
                . "</SIP>",
                '%s',
                $totalFields
            ),
            HpaMessageId::GET_DIAGNOSTIC_REPORT
        );
    }

    public function promptForSignature($transactionId = null)
    {
        return $this->hpaController->send(
            sprintf(
                "<SIP>"
                    . "<Version>1.0</Version>"
                    . "<ECRId>1004</ECRId>"
                    . "<Request>SignatureForm</Request>"
                    . "<RequestId>%s</RequestId>"
                    . "<FormText>PLEASE SIGN BELOW</FormText>"
                . "</SIP>",
                '%s'
            ),
            HpaMessageId::SIGNATURE_FORM
        );
    }

    public function getLastResponse()
    {
        return $this->hpaController->send(
            sprintf(
                "<SIP>"
                    . "<Version>1.0</Version>"
                    . "<ECRId>1004</ECRId>"
                    . "<Request>GetLastResponse</Request>"
                    . "<RequestId>%s</RequestId>"
                . "</SIP>",
                '%s'
            ),
            HpaMessageId::GET_LAST_RESPONSE
        );
    }
}
