<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\DeviceMessage;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\UPA\Interfaces\UpaMicInterface;
use GlobalPayments\Api\Terminals\UPA\Interfaces\UpaTcpInterface;
use GlobalPayments\Api\Terminals\UPA\Responses\UpaMitcResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\UpaTransactionResponse;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestParamFields;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestTransactionFields;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Terminals\TerminalResponse;

/*
 * Main controller class for Unified payment application
 *
 */

class UpaController extends DeviceController
{
    /** @var UpaInterface  */
    public $device;

    public $deviceConfig;

    /*
     * Create interface based on connection mode TCP / HTTP
     */
    public function __construct(ConnectionConfig $config)
    {
        parent::__construct($config);
        $this->device = new UpaInterface($this);
        $this->requestIdProvider = $config->requestIdProvider;
        $this->deviceConfig = $config;

        switch ($config->connectionMode) {
            case ConnectionModes::TCP_IP:
            case ConnectionModes::SSL_TCP:
                $this->deviceInterface = new UpaTcpInterface($config);
                break;
            case ConnectionModes::MIC:
                $this->deviceInterface = new UpaMicInterface($config);
                break;
            default:
                throw new ConfigurationException('Unsupported connection mode.');
        }

    }

    public function configureInterface() : IDeviceInterface
    {
        if (empty($this->device)) {
            $this->device = new UpaInterface($this);
        }

        return $this->device;
    }

    public function manageTransaction(TerminalManageBuilder $builder) : TerminalResponse
    {
        $request = $this->buildManageTransaction($builder);

        return $this->doTransaction($request);
    }

    public function processTransaction(TerminalAuthBuilder $builder) : TerminalResponse
    {
        $request = $this->buildProcessTransaction($builder);

        return $this->doTransaction($request);
    }

    private function buildManageTransaction(TerminalManageBuilder $builder) : IDeviceMessage
    {
        $requestId = (!empty($builder->requestId)) ?
            $builder->requestId :
            $this->requestIdProvider->getRequestId();

        $requestTransactionFields = new RequestTransactionFields();
        $requestTransactionFields->setParams($builder);

        $requestType = $this->mapTransactionType($builder->transactionType);

        if (!is_null($requestTransactionFields) && !empty($requestTransactionFields->getElementString())) {
            $transactionFields = $requestTransactionFields->getElementString();
        }

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => $requestType,
                'requestId' => $requestId,
                'EcrId' => $builder->ecrId ?? 13,
                'data' => [
                    'transaction' => $transactionFields ?? null
                ]
            ]
        ];
        return TerminalUtils::buildUpaRequest($requestMessage);
    }

    private function buildProcessTransaction(TerminalAuthBuilder $builder) : DeviceMessage
    {
        $requestId = (!empty($builder->requestId)) ?
            $builder->requestId :
            $this->requestIdProvider->getRequestId();

        $requestParamFields = new RequestParamFields();
        $requestParamFields->setParams($builder);

        $requestTransactionFields = new RequestTransactionFields();
        $requestTransactionFields->setParams($builder);

        $requestType = $this->mapTransactionType($builder->transactionType);

        if (!is_null($requestParamFields) && !empty($requestParamFields->getElementString())) {
            $data['params'] = $requestParamFields->getElementString();
        }

        if (!is_null($requestTransactionFields) && !empty($requestTransactionFields->getElementString())) {
            $data['transaction'] = $requestTransactionFields->getElementString();
        }

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => $requestType,
                'requestId' => $requestId,
                'EcrId' => $builder->ecrId ?? 13,
            ]
        ];
        if (!empty($data)) {
            $requestMessage['data']['data'] = $data;
        }

        return TerminalUtils::buildUpaRequest($requestMessage);
    }

    private function mapTransactionType($type)
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
            case TransactionType::AUTH:
                return UpaMessageId::PRE_AUTH;
            case TransactionType::CAPTURE:
                return UpaMessageId::CAPTURE;
            case TransactionType::TOKENIZE:
                return UpaMessageId::TOKENIZE;
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
    private function doTransaction(IDeviceMessage $request)
    {
        $request->awaitResponse = true;
        $response = $this->connector->send($request);
        if (empty($response)) {
            return null;
        }

        return new TransactionResponse($response);
    }

    public function processReport(TerminalReportBuilder $builder) : TerminalResponse
    {
        return false;
    }

    public function configureConnector(): IDeviceCommInterface
    {
        switch ($this->settings->getConnectionMode())
        {
            case ConnectionModes::TCP_IP:
                return new UpaTcpInterface($this->settings);
            case ConnectionModes::HTTP:
            case ConnectionModes::SERIAL:
            case ConnectionModes::SSL_TCP:
            case ConnectionModes::MIC:
                return new UpaMicInterface($this->settings);
            default:
                throw  new NotImplementedException();
        }
    }
}
