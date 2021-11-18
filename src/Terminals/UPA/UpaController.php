<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\UPA\Interfaces\UpaTcpInterface;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestParamFields;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestTransactionFields;
use GlobalPayments\Api\Terminals\UPA\Responses\UpaDeviceResponse;

/*
 * Main controller class for Unified payment application
 *
 */

class UpaController extends DeviceController
{

    public $device;
    public $deviceConfig;

    /*
     * Create interface based on connection mode TCP / HTTP
     */
    public function __construct(ConnectionConfig $config)
    {
        $this->device = new UpaInterface($this);
        $this->requestIdProvider = $config->requestIdProvider;
        $this->deviceConfig = $config;

        switch ($config->connectionMode) {
            case ConnectionModes::TCP_IP:
            case ConnectionModes::SSL_TCP:
                $this->deviceInterface = new UpaTcpInterface($config);
                break;
        }
    }

    /*
     * Send control message to device
     *
     * @param string $message control message to device
     *
     * @return UpaResponse parsed device response
     */
    public function send($message, $requestType = null)
    {
        //send message to gateway
        return $this->deviceInterface->send($message, $requestType);
    }

    public function manageTransaction($builder)
    {
        $requestId = (!empty($builder->requestId)) ?
                        $builder->requestId :
                        $this->requestIdProvider->getRequestId();
        
        $requestTransactionFields = new RequestTransactionFields();
        $requestTransactionFields->setParams($builder);
        
        $transactionType = $this->mapTransactionType($builder->transactionType);
        return $this->doTransaction(
            $transactionType,
            $requestId,
            null,
            $requestTransactionFields
        );
    }

    public function processTransaction($builder)
    {
        $requestId = (!empty($builder->requestId)) ?
                        $builder->requestId :
                        $this->requestIdProvider->getRequestId();

        $requestParamFields = new RequestParamFields();
        $requestParamFields->setParams($builder);
        
        $requestTransactionFields = new RequestTransactionFields();
        $requestTransactionFields->setParams($builder);
       
        $transactionType = $this->mapTransactionType($builder->transactionType);
        return $this->doTransaction(
            $transactionType,
            $requestId,
            $requestParamFields,
            $requestTransactionFields
        );
    }

    private function mapTransactionType($type, $requestToken = null)
    {
        switch ($type) {
            case TransactionType::SALE:
                return UpaMessageId::SALE;
            case TransactionType::VOID:
                return UpaMessageId::VOID;
            case TransactionType::REFUND:
                return UpaMessageId::REFUND;
            case TransactionType::EDIT:
                return UpaMessageId::TIPADJUST;
            case TransactionType::VERIFY:
                return UpaMessageId::CARD_VERIFY;
            case TransactionType::REVERSAL:
                return UpaMessageId::REVERSAL;
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
           
    private function doTransaction(
        $requestType,
        $requestId,
        RequestParamFields $requestParamFields = null,
        RequestTransactionFields $requestTransactionFields = null
    ) {
    
        $data = [];
        
        if (!is_null($requestParamFields) && !empty($requestParamFields->getElementString())) {
            $data['params'] = $requestParamFields->getElementString();
        }
        
        if (!is_null($requestTransactionFields) && !empty($requestTransactionFields->getElementString())) {
            $data['transaction'] = $requestTransactionFields->getElementString();
        }
        
        $message = TerminalUtils::buildUPAMessage($requestType, $requestId, $data);
        
        $response = $this->send($message, $requestType);
        return new UpaDeviceResponse($response, $requestType);
    }
        
    public function processReport($builder)
    {
        return false;
    }
}
