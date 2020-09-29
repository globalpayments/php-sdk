<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;

class ConnectionConfig
{
    public $deviceType;
    public $connectionMode;
    public $baudRate;
    public $parity;
    public $stopBits;
    public $dataBits;
    public $ipAddress;
    public $port;
    
    public $timeout;
    
    /*
     * Implementation of IRequestIdProvider to generate request id for each transaction
     */
    public $requestIdProvider;
    
    /*
     * Implementation of ILogManagement to generate logs for each transaction
     */
    public $logManagementProvider;

    public function validate()
    {
        if ($this->connectionMode == ConnectionModes::HTTP ||
                $this->connectionMode == ConnectionModes::TCP_IP) {
            if (empty($this->ipAddress)) {
                throw new ConfigurationException(
                    "IpAddress is required for TCP or HTTP communication modes."
                );
            }
        }

        if (empty($this->port)) {
            throw new ConfigurationException(
                "Port is required for TCP or HTTP communication modes."
            );
        }
        
        if ($this->deviceType == DeviceType::HPA_ISC250 &&
                empty($this->requestIdProvider)
        ) {
            throw new ConfigurationException(
                "Request id is mandatory for this transaction. IRequestIdProvider is not implemented"
            );
        }
    }
}
