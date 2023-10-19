<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Diamond\Responses\DiamondCloudResponse;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\StringUtils;

class DeviceCloudService
{
    private ConnectionConfig $config;

    public function __construct(ConnectionConfig $config)
    {
        $this->config = $config;
        ServicesContainer::configureService($config);
    }

    public function parseResponse($response) : TerminalResponse
    {
        if (empty($response)) {
            throw new ApiException("Enable to parse : empty response");
        }
        if (StringUtils::isJson($response) !== true) {
            throw new ApiException("Unexpected response format!");
        }

        switch ($this->config->getConnectionMode())
        {
            case ConnectionModes::DIAMOND_CLOUD:
                return new DiamondCloudResponse($response);
            default:
                throw new UnsupportedTransactionException("The selected gateway does not support this response type!");
        }
    }
}