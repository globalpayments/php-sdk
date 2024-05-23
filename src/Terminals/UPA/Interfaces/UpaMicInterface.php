<?php

namespace GlobalPayments\Api\Terminals\UPA\Interfaces;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalConfiguration;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Utils\ArrayUtils;
use GlobalPayments\Api\Utils\GenerationUtils;

class UpaMicInterface implements IDeviceCommInterface
{
    /** @var ITerminalConfiguration */
    private $config;
    /** @var GpApiConfig */
    private $gatewayConfig;
    /** @var GpApiConnector */
    private $connector;

    public function __construct(ITerminalConfiguration $config)
    {
        $this->config = $config;
        $this->gatewayConfig = $config->getGatewayConfig();
    }

    public function connect()
    {
        $this->connector = ServicesContainer::instance()->getClient($this->config->getConfigName());
        if (empty($this->gatewayConfig->accessTokenInfo->accessToken)) {
            $this->connector->signIn();
        }
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }

    /**
     * @param IDeviceMessage $message
     * @param null $requestType
     * @return mixed
     * @throws GatewayException
     */
    public function send($message, $requestType = null)
    {
        $this->connect();
        try {
            $requestData = [
                'merchant_id' => $this->gatewayConfig->merchantId ?? $this->gatewayConfig->accessTokenInfo->merchantId,
                'account_id' => $this->gatewayConfig->accessTokenInfo->transactionProcessingAccountID,
                'account_name' => $this->gatewayConfig->accessTokenInfo->transactionProcessingAccountName ?? '',
                'channel' => $this->gatewayConfig->channel,
                'country' => $this->gatewayConfig->country,
                'currency' => $this->gatewayConfig->deviceCurrency,
                'reference' => $message->getRequestField('tranNo') ?? GenerationUtils::generateOrderId(),
                'request' => $message->getJsonRequest(),
                'notifications' => [
                    'status_url' => $this->gatewayConfig->methodNotificationUrl
                ]
            ];
            $out = $this->connector->processPassThrough($requestData);

            if (!is_null($this->config->logManagementProvider)) {
                TerminalUtils::manageLog($this->config->logManagementProvider, GpApiRequest::DEVICE_ENDPOINT);
                TerminalUtils::manageLog($this->config->logManagementProvider, 'Request body:' . json_encode(ArrayUtils::array_remove_empty($requestData), JSON_UNESCAPED_SLASHES));
                TerminalUtils::manageLog($this->config->logManagementProvider, 'Response:' . json_encode($out));
            }
            return $this->parseResponse($out);
        }  catch (\Exception $e) {
            throw new GatewayException(
                'Device error: ' . $e->getMessage(),
                null,
                $e->getMessage()
            );
        }
    }

    public function parseResponse($gatewayResponse)
    {
        $gatewayResponse = $this->arrayCastRecursive($gatewayResponse);
        $gatewayResponse['provider'] = $this->gatewayConfig->gatewayProvider;

        return $gatewayResponse;
    }

    private function arrayCastRecursive($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arrayCastRecursive($value);
                }
                if (is_object($value)) {
                    $array[$key] = $this->arrayCastRecursive((array)$value);
                }
            }
        }

        if (is_object($array)) {
            return $this->arrayCastRecursive((array)$array);
        }

        return $array;
    }


}