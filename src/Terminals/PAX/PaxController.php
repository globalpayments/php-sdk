<?php

namespace GlobalPayments\Api\Terminals\PAX;

use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\PAX\Interfaces\PaxTcpInterface;
use GlobalPayments\Api\Terminals\PAX\PaxInterface;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AmountRequest;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AccountRequest;
use GlobalPayments\Api\Terminals\PAX\SubGroups\TraceRequest;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AvsRequest;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\PAX\SubGroups\ExtDataSubGroup;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxExtData;
use GlobalPayments\Api\Terminals\PAX\SubGroups\CommercialRequest;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxTxnType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxCreditResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxDebitResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\EcomSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\CashierSubGroup;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxGiftResponse;
use GlobalPayments\Api\Terminals\PAX\Interfaces\PaxHttpInterface;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\TerminalReportType;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxLocalReportResponse;
use GlobalPayments\Api\Terminals\PAX\Responses\PaxEBTResponse;

/*
 * Main controller class for Heartland payment application
 *
 */

class PaxController extends DeviceController
{

    public $device;
    public $deviceConfig;

    /*
     * Create interface based on connection mode TCP / HTTP
     */

    public function __construct(ConnectionConfig $config)
    {
        $this->device = new PaxInterface($this);
        $this->requestIdProvider = $config->requestIdProvider;
        $this->deviceConfig = $config;

        switch ($config->connectionMode) {
            case ConnectionModes::TCP_IP:
            case ConnectionModes::SSL_TCP:
                $this->deviceInterface = new PaxTcpInterface($config);
                break;
            case ConnectionModes::HTTP:
            case ConnectionModes::HTTPS:
                $this->deviceInterface = new PaxHttpInterface($config);
                break;
        }
    }

    /*
     * Send control message to device
     *
     * @param string $message control message to device
     *
     * @return PaxResponse parsed device response
     */

    public function send($message, $requestType = null)
    {
        //send message to gateway
        return $this->deviceInterface->send(trim($message), $requestType);
    }

    public function manageTransaction($builder)
    {
        $requestId = (!empty($builder->requestId)) ?
                        $builder->requestId :
                        $this->requestIdProvider->getRequestId();

        $amount = new AmountRequest();
        $account = new AccountRequest();
        $extData = new ExtDataSubGroup();
        $trace = new TraceRequest();
        $trace->referenceNumber = $requestId;
        
        $amount->transactionAmount = TerminalUtils::formatAmount($builder->amount);

        if ($builder->paymentMethod != null) {
            if ($builder->paymentMethod instanceof TransactionReference) {
                $transactionReference = $builder->paymentMethod;
                if (!empty($transactionReference->transactionId)) {
                    $extData->details[PaxExtData::HOST_REFERENCE_NUMBER] =
                            $transactionReference->transactionId;
                }
            } elseif ($builder->paymentMethod instanceof GiftCard) {
                $card = $builder->paymentMethod;
                $account->accountNumber = $card->number;
            }
        }
        $transactionType = $this->mapTransactionType($builder->transactionType);
        switch ($builder->paymentMethodType) {
            case PaymentMethodType::CREDIT:
                return $this->doCredit(
                    $transactionType,
                    $amount,
                    $account,
                    $trace,
                    new AvsRequest(),
                    new CashierSubGroup(),
                    new CommercialRequest(),
                    new EcomSubGroup(),
                    $extData
                );
            case PaymentMethodType::GIFT:
                $messageId = ($builder->currency == CurrencyType::CURRENCY) ?
                                PaxMessageId::T06_DO_GIFT : PaxMessageId::T08_DO_LOYALTY;
                return $this->doGift(
                    $messageId,
                    $transactionType,
                    $amount,
                    $account,
                    $trace,
                    new CashierSubGroup(),
                    $extData
                );
        }
    }

    public function processTransaction($builder)
    {
        
        $requestId = (!empty($builder->requestId)) ?
                        $builder->requestId :
                        $this->requestIdProvider->getRequestId();

        $amount = new AmountRequest();
        $account = new AccountRequest();
        $extData = new ExtDataSubGroup();
        $trace = new TraceRequest();
        $commercial = new CommercialRequest();
        $ecom = new EcomSubGroup();
        $cashier = new CashierSubGroup();
        $avs = new AvsRequest();
        
        $amount->transactionAmount = TerminalUtils::formatAmount($builder->amount);
        $amount->tipAmount = TerminalUtils::formatAmount($builder->gratuity);
        $amount->cashBackAmount = TerminalUtils::formatAmount($builder->cashBackAmount);
        $amount->taxAmount = TerminalUtils::formatAmount($builder->taxAmount);
        
        $trace->referenceNumber = $requestId;
        $trace->invoiceNumber = $builder->invoiceNumber;
        if (!empty($builder->clientTransactionId)) {
            $trace->clientTransactionId = $builder->clientTransactionId;
        }
        
        if ($builder->paymentMethod != null) {
            if ($builder->paymentMethod instanceof CreditCardData) {
                $card = $builder->paymentMethod;
                if (empty($card->token)) {
                    $account->accountNumber = $card->number;
                    $account->expd = $card->getShortExpiry();
                    if ($builder->transactionType != TransactionType::VERIFY &&
                            $builder->transactionType != TransactionType::REFUND) {
                        $account->cvvCode = $card->cvn;
                    }
                } else {
                    $extData->details[PaxExtData::TOKEN] = $card->token;
                }
            } elseif ($builder->paymentMethod instanceof TransactionReference) {
                $reference = $builder->paymentMethod;
                if (!empty($reference->authCode)) {
                    $trace->authCode = $reference->authCode;
                }
                if (!empty($reference->transactionId)) {
                    $extData->details[PaxExtData::HOST_REFERENCE_NUMBER] = $reference->transactionId;
                }
            } elseif ($builder->paymentMethod instanceof GiftCard) {
                $card = $builder->paymentMethod;
                $account->accountNumber = $card->number;
            }
        }
        
        if ($builder->allowDuplicates !== null) {
            $account->dupOverrideFlag = 1;
        }
        
        if ($builder->address !== null) {
            $avs->address = $builder->address->streetAddress1;
            $avs->zipCode = $builder->address->postalCode;
        }
        $commercial->customerCode = $builder->customerCode;
        $commercial->poNumber = $builder->poNumber;
        $commercial->taxExempt = $builder->taxExempt;
        $commercial->taxExemptId = $builder->taxExemptId;
        
        if ($builder->requestMultiUseToken !== null) {
            $extData->details[PaxExtData::TOKEN_REQUEST] = $builder->requestMultiUseToken;
        }
        
        if ($builder->signatureCapture !== null) {
            $extData->details[PaxExtData::SIGNATURE_CAPTURE] = $builder->signatureCapture;
        }
        
        $transactionType = $this->mapTransactionType($builder->transactionType, $builder->requestMultiUseToken);
        switch ($builder->paymentMethodType) {
            case PaymentMethodType::CREDIT:
                return $this->doCredit(
                    $transactionType,
                    $amount,
                    $account,
                    $trace,
                    $avs,
                    $cashier,
                    $commercial,
                    $ecom,
                    $extData
                );
            case PaymentMethodType::DEBIT:
                return $this->doDebit(
                    $transactionType,
                    $amount,
                    $account,
                    $trace,
                    $cashier,
                    $extData
                );
            case PaymentMethodType::GIFT:
                $messageId = ($builder->currency == CurrencyType::CURRENCY) ?
                                PaxMessageId::T06_DO_GIFT : PaxMessageId::T08_DO_LOYALTY;
                return $this->doGift($messageId, $transactionType, $amount, $account, $trace, $cashier, $extData);
                
            case PaymentMethodType::EBT:
                if (!empty($builder->currency)) {
                    $account->ebtType = substr($builder->currency, 0, 1);
                }
                return $this->doEBT($transactionType, $amount, $account, $trace, $cashier, $extData);
        }
    }

    private function mapTransactionType($type, $requestToken = null)
    {
        switch ($type) {
            case TransactionType::ADD_VALUE:
                return PaxTxnType::ADD;
            case TransactionType::AUTH:
                return PaxTxnType::AUTH;
            case TransactionType::BALANCE:
                return PaxTxnType::BALANCE;
            case TransactionType::CAPTURE:
                return PaxTxnType::POSTAUTH;
            case TransactionType::REFUND:
                return PaxTxnType::RETURN_REQUEST;
            case TransactionType::SALE:
                return PaxTxnType::SALE_REDEEM;
            case TransactionType::VERIFY:
                return $requestToken ? PaxTxnType::TOKENIZE : PaxTxnType::VERIFY;
            case TransactionType::VOID:
                return PaxTxnType::VOID;
            case TransactionType::BENEFIT_WITHDRAWAL:
                return PaxTxnType::WITHDRAWAL;
            case TransactionType::REVERSAL:
                return PaxTxnType::REVERSAL;
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
    
    private function doCredit(
        $transactionType,
        $amounts,
        $accounts,
        $trace,
        $avs,
        $cashier,
        $commercial,
        $ecom,
        $extData
    ) {
    
        $commands = [
            PaxMessageId::T00_DO_CREDIT,
            '1.35',
            $transactionType,
            $amounts->getElementString(),
            $accounts->getElementString(),
            $trace->getElementString(),
            $avs->getElementString(),
            $cashier->getElementString(),
            $commercial->getElementString(),
            $ecom->getElementString(),
            $extData->getElementString(),
        ];
        $response = $this->doTransaction($commands, PaxMessageId::T00_DO_CREDIT);
        return new PaxCreditResponse($response);
    }
    
    private function doTransaction($commands, $requestType = null)
    {
        $message = implode(chr(ControlCodes::FS), $commands);
        $finalMessage = TerminalUtils::buildMessage($message);
        return $this->send($finalMessage, $requestType);
    }
    
    private function doDebit($transactionType, $amounts, $accounts, $trace, $cashier, $extData)
    {
        $commands = [
            PaxMessageId::T02_DO_DEBIT,
            '1.35',
            $transactionType,
            $amounts->getElementString(),
            $accounts->getElementString(),
            $trace->getElementString(),
            $cashier->getElementString(),
            $extData->getElementString(),
        ];
        $response = $this->doTransaction($commands, PaxMessageId::T02_DO_DEBIT);
        return new PaxDebitResponse($response);
    }
    
    private function doGift($messageId, $transactionType, $amounts, $accounts, $trace, $cashier, $extData)
    {
        $commands = [
            $messageId,
            '1.35',
            $transactionType,
            $amounts->getElementString(),
            $accounts->getElementString(),
            $trace->getElementString(),
            $cashier->getElementString(),
            $extData->getElementString(),
        ];
        $response = $this->doTransaction($commands, $messageId);
        return new PaxGiftResponse($response);
    }
    
    public function processReport($builder)
    {
        $response = $this->buildReportTransaction($builder);
        return new PaxLocalReportResponse($response);
    }
    
    public function buildReportTransaction($builder)
    {
        $messageId = $this->mapReportType($builder->reportType);
        
        switch ($builder->reportType) {
            case TerminalReportType::LOCAL_DETAIL_REPORT:
                $criteria = $builder->searchBuilder;
                $extData = new ExtDataSubGroup();
                if (!empty($criteria->MerchantId)) {
                    $extData->details[PaxExtData::MERCHANT_ID] = $criteria->MerchantId;
                }
                
                if (!empty($criteria->MerchantName)) {
                    $extData->details[PaxExtData::MERCHANT_NAME] = $criteria->MerchantName;
                }
                
                $commands = [
                    $messageId,
                    '1.35',
                    '00',
                    (isset($criteria->TransactionType)) ? $criteria->TransactionType : '',
                    (isset($criteria->CardType)) ? $criteria->CardType : '',
                    (isset($criteria->RecordNumber)) ? $criteria->RecordNumber : '',
                    (isset($criteria->TerminalReferenceNumber)) ? $criteria->TerminalReferenceNumber : '',
                    (isset($criteria->AuthCode)) ? $criteria->AuthCode : '',
                    (isset($criteria->ReferenceNumber)) ? $criteria->ReferenceNumber : '',
                    $extData->getElementString(),
                ];
                return $this->doTransaction($commands, $messageId);
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
    
    private function mapReportType($type)
    {
        switch ($type) {
            case TerminalReportType::LOCAL_DETAIL_REPORT:
                return PaxMessageId::R02_LOCAL_DETAIL_REPORT;
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
    private function doEBT($transactionType, $amounts, $accounts, $trace, $cashier, $extData)
    {
        $commands = [
            PaxMessageId::T04_DO_EBT,
            '1.35',
            $transactionType,
            $amounts->getElementString(),
            $accounts->getElementString(),
            $trace->getElementString(),
            $cashier->getElementString(),
            $extData->getElementString(),
        ];
        $response = $this->doTransaction($commands);
        return new PaxEBTResponse($response);
    }
}
