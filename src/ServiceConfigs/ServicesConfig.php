<?php

namespace GlobalPayments\Api\ServiceConfigs;

class ServicesConfig
{
    /** @var GatewayConfig */
    public $gatewayConfig;

    /** @var ConnectionConfig */
    public $deviceConnectionConfig;

    /** @var TableServiceConfig */
    public $tableServiceConfig;

    /** @var PayrollConfig */
    public $payrollConfig;

    /** @var BoardingConfig */
    public $boardingConfig;

    /** @var int */
    public $timeout;

    public function validate()
    {
        if (!empty($this->gatewayConfig)) {
            $this->gatewayConfig->validate();
        }

        if (!empty($this->deviceConnectionConfig)) {
            $this->deviceConnectionConfig->validate();
        }

        if (!empty($this->tableServiceConfig)) {
            $this->tableServiceConfig->validate();
        }

        if (!empty($this->payrollConfig)) {
            $this->payrollConfig->validate();
        }
    }
}
