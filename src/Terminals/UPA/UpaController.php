<?php

namespace GlobalPayments\Api\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\DeviceMessage;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Enums\TerminalReportType;
use GlobalPayments\Api\Terminals\UPA\Interfaces\UpaMicInterface;
use GlobalPayments\Api\Terminals\UPA\Interfaces\UpaTcpInterface;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchList;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchReportResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\OpenTabDetailsResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\SafReportResponse;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestHostFields;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestLodgingFields;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestParamFields;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestProcessingIndicatorsFields;
use GlobalPayments\Api\Terminals\UPA\SubGroups\RequestTransactionFields;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\ArrayUtils;

/*
 * Main controller class for Unified payment application
 *
 */

class UpaController extends DeviceController
{
    public UpaInterface $device;
    public ConnectionConfig $deviceConfig;

    /*
     * Create interface based on connection mode TCP / HTTP
     */
    public function __construct(ConnectionConfig $config)
    {
        parent::__construct($config);
        $this->requestIdProvider = $config->requestIdProvider;
    }

    public function configureInterface() : IDeviceInterface
    {
        if (empty($this->deviceInterface)) {
            $this->deviceInterface = new UpaInterface($this);
        }

        return $this->deviceInterface;
    }

    public function manageTransaction(TerminalManageBuilder $builder) : TerminalResponse
    {
        /** @var DeviceMessage $request */
        $request = $this->buildManageTransaction($builder);
        $this->checkRequest($request->getJsonRequest());

        return $this->doTransaction($request);
    }

    public function processTransaction(TerminalAuthBuilder $builder) : TerminalResponse
    {
        $request = $this->buildProcessTransaction($builder);
        $this->checkRequest($request->getJsonRequest());

        return $this->doTransaction($request);
    }

    private function checkRequest(array $request): void
    {
        $command = $request['data']['command'] ?? null;
        switch ($command)
        {
            case UpaMessageId::UPDATE_LODGING_DETAILS:
                $this->deviceInterface->validations->setMandatoryParams(['referenceNumber', 'amount']);
                break;
            case UpaMessageId::START_CARD_TRANSACTION:
                $this->deviceInterface->validations->setMandatoryParams(
                    ['acquisitionTypes', 'quickChip', 'totalAmount', 'transactionType']
                );
                break;
            case UpaMessageId::CONTINUE_EMV_TRANSACTION:
                $this->deviceInterface->validations->setMandatoryParams(['merchantDecision', 'quickChip', 'totalAmount']);
                break;
            case UpaMessageId::COMPLETE_EMV_TRANSACTION:
                $this->deviceInterface->validations->setMandatoryParams(['quickChip', 'hostDecision']);
                break;
            case UpaMessageId::PROCESS_CARD_TRANSACTION:
                $this->deviceInterface->validations->setMandatoryParams(
                    ['acquisitionTypes', 'merchantDecision', 'quickChip', 'totalAmount', 'transactionType']
                );
                break;
            case UpaMessageId::CONTINUE_CARD_TRANSACTION:
                $this->deviceInterface->validations->setMandatoryParams(['merchantDecision', 'totalAmount']);
                break;
            default:
                break;
        }

        if (($missingParams = $this->deviceInterface->validations->validate($request)) !== true) {
            throw new ArgumentException(sprintf('Mandatory params missing: %s !', $missingParams));
        }
    }

    private function buildManageTransaction(TerminalManageBuilder $builder) : IDeviceMessage
    {
        $requestId = $builder->requestId ?? $this->requestIdProvider->getRequestId();
        $requestType = $this->mapTransactionType($builder->transactionType, $builder->transactionModifier);

        $requestTransactionFields = new RequestTransactionFields();
        $requestTransactionFields->setParams($builder);

        if (isset($builder->lodgingData)) {
            $lodgingFields = new RequestLodgingFields();
            $lodgingFields->setParams($builder->lodgingData, $requestType);
        }

        if (!is_null($requestTransactionFields) && !empty($requestTransactionFields->getElementString())) {
            $transactionFields = $requestTransactionFields->getElementString();
        }

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => $requestType,
                'requestId' => $requestId,
                'EcrId' => $builder->ecrId ?? ($this->deviceInterface->ecrId ?? '1'),
                'data' => [
                    'transaction' => $transactionFields ?? null,
                    'lodging' => $lodgingFields ?? null
                ]
            ]
        ];
        $requestMessage = ArrayUtils::array_remove_empty($requestMessage);

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

        $requestProcessingFields = new RequestProcessingIndicatorsFields();
        $requestProcessingFields->setParams($builder);

        $requestHostFields = new RequestHostFields();
        $requestHostFields->setParams($builder);

        $requestType = $this->mapTransactionType($builder->transactionType, $builder->transactionModifier, $builder->paymentMethod);

        if (!is_null($requestParamFields) && !empty($requestParamFields->getElementString())) {
            $data['params'] = $requestParamFields->getElementString();
        }

        if (!empty($requestProcessingFields->getElementString())) {
            $data['processingIndicators'] = $requestProcessingFields->getElementString();
        }

        if (!empty($requestTransactionFields->getElementString())) {
            $data['transaction'] = $requestTransactionFields->getElementString();
        }

        if (!empty($requestHostFields->getElementString())) {
            $data['host'] = $requestHostFields->getElementString();
        }

        $requestMessage = [
            'message' => UpaMessageType::MSG,
            'data' => [
                'command' => $requestType,
                'requestId' => $requestId,
                'EcrId' => $builder->ecrId ?? ($this->deviceInterface->ecrId ?? '1'),
            ]
        ];
        if (!empty($data)) {
            $requestMessage['data']['data'] = $data;
        }
        $requestMessage = ArrayUtils::array_remove_empty($requestMessage);

        return TerminalUtils::buildUpaRequest($requestMessage);
    }

    private function mapTransactionType($type, $trnModifier = null, IPaymentMethod $paymentMethod = null)
    {
        switch ($type) {
            case TransactionType::SALE:
                switch ($trnModifier) {
                    case TransactionModifier::START_TRANSACTION:
                        return UpaMessageId::START_CARD_TRANSACTION;
                    case TransactionModifier::PROCESS_TRANSACTION:
                        return UpaMessageId::PROCESS_CARD_TRANSACTION;
                    default:
                        if (!empty($paymentMethod->entryMethod)) {
                            switch ($paymentMethod->entryMethod) {
                                case ManualEntryMethod::MAIL:
                                    return UpaMessageId::MAIL_ORDER;
                                case ManualEntryMethod::PHONE:
                                    return UpaMessageId::FORCE_SALE;
                                default:
                                    break;
                            }
                        }
                        return UpaMessageId::SALE;
                }
            case TransactionType::VOID:
                return UpaMessageId::VOID;
            case TransactionType::BALANCE:
                return UpaMessageId::BALANCE_INQUIRY;
            case TransactionType::REFUND:
                switch ($trnModifier) {
                    case TransactionModifier::START_TRANSACTION:
                        return UpaMessageId::START_CARD_TRANSACTION;
                    case TransactionModifier::PROCESS_TRANSACTION:
                        return UpaMessageId::PROCESS_CARD_TRANSACTION;
                    default:
                        return UpaMessageId::REFUND;
                }
            case TransactionType::EDIT:
                switch ($trnModifier) {
                    case TransactionModifier::UPDATE_TAX_DETAILS:
                        $messageId = UpaMessageId::UPDATE_TAX_INFO;
                        break;
                    case TransactionModifier::UPDATE_LODGING_DETAILS:
                        $messageId = UpaMessageId::UPDATE_LODGING_DETAILS;
                        break;
                    default:
                        $messageId = UpaMessageId::TIPADJUST;
                        break;
                }
                return $messageId;
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
            case TransactionType::DELETE:
                if ($trnModifier == TransactionModifier::DELETE_PRE_AUTH) {
                    return UpaMessageId::DELETE_PRE_AUTH;
                }
                break;
            case TransactionType::CONFIRM:
                switch ($trnModifier)
                {
                    case TransactionModifier::CONTINUE_EMV_TRANSACTION:
                        return UpaMessageId::CONTINUE_EMV_TRANSACTION;
                    case TransactionModifier::CONTINUE_CARD_TRANSACTION:
                        return UpaMessageId::CONTINUE_CARD_TRANSACTION;
                    default:
                        return UpaMessageId::COMPLETE_EMV_TRANSACTION;
                }
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
            throw new GatewayException('No gateway response!');
        }

        return new TransactionResponse($response);
    }

    /**
     * @param TerminalReportBuilder $builder
     * @return ITerminalReport
     * @throws GatewayException
     */
    public function processReport(TerminalReportBuilder $builder) : ITerminalReport
    {
        $response = $this->connector->send($this->buildReportTransaction($builder));
        if (empty($response)) {
            throw new GatewayException('No gateway response!');
        }

        $jsonResponse = json_decode(json_encode($response));
        switch ($builder->reportType) {
            case TerminalReportType::GET_SAF_REPORT:
                return new SafReportResponse($jsonResponse);
            case TerminalReportType::GET_BATCH_REPORT:
            case TerminalReportType::GET_BATCH_DETAILS:
                return new BatchReportResponse($jsonResponse);
            case TerminalReportType::FIND_BATCHES:
                return new BatchList($jsonResponse);
            case TerminalReportType::GET_OPEN_TAB_DETAILS:
                return new OpenTabDetailsResponse($jsonResponse);
            default:
                throw new GatewayException('Unknown report type!');
        }
    }

    private function buildReportTransaction(TerminalReportBuilder $builder) : IDeviceMessage
    {
        $requestId = $builder->searchBuilder->referenceNumber;
        if (empty($requestId) && isset($this->requestIdProvider)) {
            $requestId = $this->requestIdProvider->getRequestId();
        }
        $requestMessage = [
            "message" => "MSG",
            "data" => [
                "command" => $this->mapReportType($builder->reportType),
                "EcrId" => $builder->searchBuilder->ecrId ?? ($this->deviceInterface->ecrId ?? null),
                "requestId" => $requestId
            ]
        ];

        switch ($builder->reportType) {
            case TerminalReportType::GET_BATCH_REPORT:
            case TerminalReportType::FIND_BATCHES:
                $requestMessage['data']["params"] = [
                        "batch" => $builder->searchBuilder->batch ?? null
                    ];
                break;
            case TerminalReportType::GET_BATCH_DETAILS:
                $requestMessage['data']["params"] = [
                    "reportOutput" => $builder->searchBuilder->reportOutput ?? null,
                    "reportType" => $builder->searchBuilder->reportType ?? null,
                    "batch" => $builder->searchBuilder->batch ?? null
                ];
                break;
            case TerminalReportType::GET_SAF_REPORT:
                $requestMessage['data']["params"] = [
                    "reportOutput" => $builder->searchBuilder->reportOutput ?? null
                ];
                break;
            default:
                break;
        }

        return TerminalUtils::buildUpaRequest(ArrayUtils::array_remove_empty($requestMessage));
    }

    private function mapReportType(string $reportType)
    {
        switch ($reportType) {
            case TerminalReportType::GET_SAF_REPORT:
                return UpaMessageId::GET_SAF_REPORT;
            case TerminalReportType::GET_BATCH_REPORT:
                return UpaMessageId::GET_BATCH_REPORT;
            case TerminalReportType::FIND_BATCHES;
                return UpaMessageId::AVAILABLE_BATCHES;
            case TerminalReportType::GET_BATCH_DETAILS:
                return UpaMessageId::GET_BATCH_DETAILS;
            case TerminalReportType::GET_OPEN_TAB_DETAILS:
                return UpaMessageId::GET_OPEN_TAB_DETAILS;
            default:
                throw new UnsupportedTransactionException();
        }
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
            case ConnectionModes::MEET_IN_THE_CLOUD:
                if ($this->settings->getGatewayConfig() instanceof GpApiConfig) {
                    return new UpaMicInterface($this->settings);
                }
            default:
                throw  new NotImplementedException();
        }
    }
}
