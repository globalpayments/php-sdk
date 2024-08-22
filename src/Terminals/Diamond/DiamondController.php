<?php

namespace GlobalPayments\Api\Terminals\Diamond;

use GlobalPayments\Api\Builders\BaseBuilder\Validations;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilderValidations;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\Region;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\DeviceMessage;
use GlobalPayments\Api\Terminals\Diamond\Entities\DiamondCloudRequest;
use GlobalPayments\Api\Terminals\Diamond\Interfaces\DiamondHttpInterface;
use GlobalPayments\Api\Terminals\Diamond\Responses\DiamondCloudResponse;
use GlobalPayments\Api\Terminals\DiamondCloudConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\TerminalReportType;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\StringUtils;

class DiamondController extends DeviceController
{
    private DiamondInterface $device;
    private DiamondCloudConfig $config;
    private static array $endpointExceptions = [
            DiamondCloudRequest::CAPTURE_EU => Region::EU,
            DiamondCloudRequest::CANCEL_AUTH => Region::EU,
            DiamondCloudRequest::INCREASE_AUTH => Region::EU,
            DiamondCloudRequest::RECONCILIATION => Region::EU,
            DiamondCloudRequest::CAPTURE => Region::US,
            DiamondCloudRequest::EBT_FOOD => Region::US,
            DiamondCloudRequest::EBT_BALANCE => Region::US,
            DiamondCloudRequest::EBT_RETURN => Region::US,
            DiamondCloudRequest::GIFT_RELOAD => Region::US,
            DiamondCloudRequest::GIFT_BALANCE => Region::US,
            DiamondCloudRequest::GIFT_REDEEM => Region::US
    ];

    public function __construct(ConnectionConfig $config)
    {
        parent::__construct($config);
        $this->config = $config;
        $this->requestIdProvider = $config->requestIdProvider;
    }

    public function processTransaction(TerminalAuthBuilder $builder): TerminalResponse
    {
        $request = $this->buildProcessTransaction($builder);
        return $this->doTransaction($request);
    }

    public function manageTransaction(TerminalManageBuilder $builder): TerminalResponse
    {
        $request = $this->buildManageTransaction($builder);

        return $this->doTransaction($request);
    }

    public function processReport(TerminalReportBuilder $builder): ITerminalReport
    {
        $requestBuilderValidation = new RequestBuilderValidations($this->setupValidationsReport());
        $requestBuilderValidation->validate($builder, $builder->reportType);

        switch ($builder->reportType) {
            case TerminalReportType::LOCAL_DETAIL_REPORT:
                $payload = [
                    'endpoint' => "/{$this->config->posID}/details/{$builder->searchBuilder->referenceNumber}",
                    'queryParams' => [
                        'POS_ID' => $this->config->posID,
                        'cloud_id' => $builder->searchBuilder->referenceNumber
                    ],
                    'verb' => 'GET'
                ];
                break;
            default:
                throw new GatewayException('Report type not defined!');
        }
        $request = new DeviceMessage($payload);

        return $this->doTransaction($request);
    }

    public function configureInterface() : IDeviceInterface
    {
        if (empty($this->device)) {
            $this->device = new DiamondInterface($this);
        }

        return $this->device;
    }

    public function configureConnector(): IDeviceCommInterface
    {
        if ($this->settings->getConnectionMode() !== ConnectionModes::DIAMOND_CLOUD) {
            throw  new NotImplementedException();
        }

        return new DiamondHttpInterface($this->settings);
    }

    private function doTransaction(IDeviceMessage $request)
    {
        $request->awaitResponse = true;
        $response = $this->connector->send($request);
        if (empty($response)) {
            return null;
        }

        return new DiamondCloudResponse($response);
    }

    private function setupValidationsReport()
    {
        $validations = new Validations();
        $validations->of(TerminalReportType::LOCAL_DETAIL_REPORT)
            ->check('referenceNumber')->isNotNullInSubProperty('searchBuilder');

        return $validations;
    }

    private function validateEndpoint($endpoint)
    {
        if (empty($endpoint)) {
            throw new GatewayException("Payment type not supported!");
        }
        if (isset(self::$endpointExceptions[$endpoint])) {
            if (self::$endpointExceptions[$endpoint] != $this->config->region) {
                return false;
            }
        }

        return true;
    }

    private function buildProcessTransaction(TerminalAuthBuilder $builder) : DeviceMessage
    {
        $body = [];
        $endpoint = '';
        switch ($builder->transactionType) {
            case TransactionType::SALE:
                $verb = 'POST';
                switch ($builder->paymentMethodType) {
                    case PaymentMethodType::EBT:
                        $endpoint = DiamondCloudRequest::EBT_FOOD;
                        $body = [
                            'amount' => StringUtils::toNumeric($builder->amount)
                        ];
                        break;
                    case PaymentMethodType::GIFT:
                        $endpoint = DiamondCloudRequest::GIFT_REDEEM;
                        $body = [
                            'amount' => StringUtils::toNumeric($builder->amount)
                        ];
                        break;
                    default:
                        $endpoint = DiamondCloudRequest::SALE;
                        $body = [
                            'amount' => StringUtils::toNumeric($builder->amount),
                            'panDataToken' => '', //@TODO
                            'tip_amount' => StringUtils::toNumeric($builder->gratuity),
                            'cash_back' => StringUtils::toNumeric($builder->cashBackAmount)
                        ];
                }
                break;
            case TransactionType::REFUND:
                $verb = 'POST';
                if($builder->paymentMethodType === PaymentMethodType::EBT) {
                    $endpoint = DiamondCloudRequest::EBT_RETURN;
                    $body = [
                        'amount' => StringUtils::toNumeric($builder->amount)
                    ];
                } else {
                    $endpoint = DiamondCloudRequest::SALE_RETURN;
                    $body = [
                        'amount' => StringUtils::toNumeric($builder->amount),
                        'panDataToken' => '', //@TODO
                    ];
                }
                break;
            case  TransactionType::AUTH:
                $endpoint = DiamondCloudRequest::AUTH;
                $verb = 'POST';
                $body = [
                    'amount' => StringUtils::toNumeric($builder->amount),
                    'panDataToken' => '', //@TODO
                ];
                break;
            case TransactionType::BALANCE:
                if ($this->config->region !== Region::EU) {
                    $verb = 'POST';
                    if($builder->paymentMethodType === PaymentMethodType::EBT) {
                        $endpoint = DiamondCloudRequest::EBT_BALANCE;
                    }
                    if($builder->paymentMethodType === PaymentMethodType::GIFT) {
                        $endpoint = DiamondCloudRequest::GIFT_BALANCE;
                    }
                } else {
                    throw new GatewayException(sprintf(
                        "Transaction type %s for payment type not supported in %s !",
                        TransactionType::getKey(TransactionType::BALANCE),
                        $this->config->region
                    ));
                }
                break;
            case TransactionType::BATCH_CLOSE:
                $verb = 'POST';
                $endpoint = DiamondCloudRequest::RECONCILIATION;
                break;
            case TransactionType::ADD_VALUE:
                if ($builder->paymentMethodType === PaymentMethodType::GIFT) {
                    $endpoint = DiamondCloudRequest::GIFT_RELOAD;
                    $verb = 'POST';
                    $body = [
                        'amount' => StringUtils::toNumeric($builder->amount),
                    ];
                }
                break;
            default:
                throw new GatewayException(sprintf("Transaction type %s not supported!", TransactionType::getKey($builder->transactionType)));
        }

        if ($this->validateEndpoint($endpoint) === false) {
            throw new GatewayException(sprintf("This feature is not supported in %s region!", $this->config->region));
        }

        $endpoint = "/" . $this->config->posID . $endpoint;
        $request = [
            'endpoint' => $endpoint,
            'body' => $body,
            'queryParams' => [
                'POS_ID' => $this->config->posID
            ],
            'verb' => $verb
        ];

        return new DeviceMessage($request);
    }

    private function buildManageTransaction(TerminalManageBuilder $builder) : DeviceMessage
    {
        $body = [];
        switch ($builder->transactionType) {
            case TransactionType::VOID:
                $endpoint = DiamondCloudRequest::VOID;
                $verb = 'POST';
                $body = [
                    'transaction_id' => $builder->transactionId
                ];
                break;
            case TransactionType::EDIT:
                if (substr($this->config->deviceType,0,4) === "PAX_") {
                    throw new GatewayException("Tip adjust is not available on PAX devices");
                }
                $endpoint = DiamondCloudRequest::TIP_ADJUST;
                $verb = 'POST';
                $body = [
                    'tip_amount' => StringUtils::toNumeric($builder->gratuity),
                    'amount' => StringUtils::toNumeric($builder->amount),
                    'transaction_id' => $builder->transactionId
                ];
                break;
            case TransactionType::CAPTURE:
                $endpoint = DiamondCloudRequest::CAPTURE;
                if ($this->config->region === Region::EU) {
                    $endpoint = DiamondCloudRequest::CAPTURE_EU;
                }
                $verb = 'POST';
                $body = [
                    'tip_amount' => StringUtils::toNumeric($builder->gratuity),
                    'amount' => StringUtils::toNumeric($builder->amount),
                    'transaction_id' => $builder->transactionId
                ];
                break;
            case TransactionType::DELETE:
                if ($builder->transactionModifier === TransactionModifier::DELETE_PRE_AUTH) {
                    $endpoint = DiamondCloudRequest::CANCEL_AUTH;
                    $verb = 'POST';
                    $body = [
                        'transaction_id' => $builder->transactionId
                    ];
                }
                break;
            case TransactionType::AUTH:
                if ($builder->transactionModifier === TransactionModifier::INCREMENTAL) {
                    $endpoint = DiamondCloudRequest::INCREASE_AUTH;
                    $verb = 'POST';
                    $body = [
                        'amount' => StringUtils::toNumeric($builder->amount),
                        'transaction_id' => $builder->transactionId
                    ];
                }
                break;
            case TransactionType::REFUND:
                $endpoint = DiamondCloudRequest::SALE_RETURN;
                $verb = 'POST';
                $body = [
                    'transaction_id' => $builder->transactionId
                ];
                break;
            default:
                throw new GatewayException(sprintf("Transaction type %s with modifier %s not supported!",
                    TransactionType::getKey($builder->transactionType),
                    TransactionModifier::getKey($builder->transactionModifier)
                ));
        }
        if (empty($endpoint) || empty($verb)) {
            throw new GatewayException(sprintf(
                "Transaction type %s with modifier %s not supported!",
                $builder->transactionType,
                $builder->transactionModifier
            ));
        }
        if ($this->validateEndpoint($endpoint) === false) {
            throw new GatewayException(sprintf("This feature is not supported in %s region!", $this->config->region));
        }

        $endpoint = "/" . $this->config->posID . $endpoint;

        $request = [
            'endpoint' => $endpoint,
            'body' => $body,
            'queryParams' => [
                'POS_ID' => $this->config->posID
            ],
            'verb' => $verb
        ];

        return new DeviceMessage($request);
    }
}