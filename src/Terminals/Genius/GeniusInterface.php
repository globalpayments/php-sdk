<?php

namespace GlobalPayments\Api\Terminals\Genius;

use Exception;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Abstractions\ISignatureResponse;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;
use GlobalPayments\Api\Entities\Enums\{PaymentMethodType, ReportType, TransactionType};
use GlobalPayments\Api\Entities\Exceptions\{ApiException, NotImplementedException};
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\DeviceInterface;
use GlobalPayments\Api\Terminals\Genius\Builders\MitcManageBuilder;
use GlobalPayments\Api\Terminals\Genius\Entities\Enums\TransactionIdType;
use GlobalPayments\Api\Terminals\Genius\Responses\MitcResponse;

class GeniusInterface extends DeviceInterface
{
    public $geniusController;

    public function __construct(GeniusController $deviceController)
    {
        $this->geniusController = $deviceController;
    }

    /**
     * 
     * @param mixed $amount 
     * @return MitcManageBuilder 
     */
    public function refundById($amount = null) : MitcManageBuilder
    {
        return (new MitcManageBuilder(
            TransactionType::SALE,
            TransactionType::REFUND
        ))->withAmount($amount);
    }

    /**
     * 
     * @param TransactionType $transactionType 
     * @param string $transactionId 
     * @param TransactionIdType $transactionIdType 
     * @return MitcResponse 
     * @throws ApiException 
     * @throws Exception 
     */
    public function getTransactionDetail(
        $transactionType,
        string $transactionId,
        $transactionIdType = TransactionIdType::CLIENT_TRANSACTION_ID
    ) : TerminalReportBuilder
    {
        $builder = new TerminalReportBuilder(ReportType::TRANSACTION_DETAIL);
        $builder->where('transactionType', $transactionType);
        $builder->where('transactionId', $transactionId);
        $builder->where('transactionIdType', $transactionIdType);
        return $builder;
    }

    /**
     * 
     * @return MitcManageBuilder 
     */
    public function void() : MitcManageBuilder
    {
        return (new MitcManageBuilder(
            TransactionType::SALE,
            TransactionType::VOID,
            PaymentMethodType::CREDIT,
        ));
    }

    /**
     * 
     * @return MitcManageBuilder 
     */
    public function voidRefund() : MitcManageBuilder
    {
        return (new MitcManageBuilder(
            TransactionType::REFUND,
            TransactionType::VOID
        ));
    }

    public function cancel($cancelParams = null)
    {
        return new NotImplementedException();
    }

    public function getSignatureFile(SignatureData $data = null) : ISignatureResponse
    {
        return new NotImplementedException();
    }

    public function initialize()
    {
        return new NotImplementedException();
    }

    public function batchClose() : IBatchCloseResponse
    {
        return new NotImplementedException();
    }

    public function eod()
    {
        return new NotImplementedException();
    }

    public function creditAuth($amount = null)
    {
        return new NotImplementedException();
    }

    public function creditCapture($amount = null)
    {
        return new NotImplementedException();
    }

    public function creditVerify()
    {
        return new NotImplementedException();
    }

    public function creditTipAdjust($tipAmount = null)
    {
        return new NotImplementedException();
    }

    public function debitRefund($amount = null)
    {
        return new NotImplementedException();
    }

    public function ebtBalance()
    {
        return new NotImplementedException();
    }

    public function ebtPurchase($amount = null)
    {
        return new NotImplementedException();
    }

    public function ebtRefund($amount = null)
    {
        return new NotImplementedException();
    }

    public function ebtWithdrawl($amount = null)
    {
        return new NotImplementedException();
    }

    public function startDownload($deviceSettings)
    {
        return new NotImplementedException();
    }

    public function giftSale($amount = null)
    {
        return new NotImplementedException();
    }

    public function giftAddValue($amount = null)
    {
        return new NotImplementedException();
    }

    public function giftVoid()
    {
        return new NotImplementedException();
    }

    public function giftBalance()
    {
        return new NotImplementedException();
    }

    public function setSafMode($paramValue)
    {
        return new NotImplementedException();
    }

    public function safDelete($safIndicator)
    {
        return new NotImplementedException();
    }

    public function safSummaryReport($safIndicator = null)
    {
        return new NotImplementedException();
    }

    public function sendFile($sendFileData)
    {
        return new NotImplementedException();
    }

    public function getDiagnosticReport($totalFields)
    {
        return new NotImplementedException();
    }

    public function getLastResponse()
    {
        return new NotImplementedException();
    }

    public function promptForSignature($transactionId = null)
    {
        return new NotImplementedException();
    }
}
