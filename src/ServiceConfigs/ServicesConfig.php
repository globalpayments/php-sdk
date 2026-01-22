<?php

namespace GlobalPayments\Api\ServiceConfigs;

use GlobalPayments\Api\ConfiguredServices;

class ServicesConfig
{
    /** @var GatewayConfig */
    public mixed $gatewayConfig = null;

    /** @var ConnectionConfig */
    public mixed $deviceConnectionConfig = null;

    /** @var TableServiceConfig */
    public mixed $tableServiceConfig = null;

    /** @var PayrollConfig */
    public mixed $payrollConfig = null;

    /** @var BoardingConfig */
    public mixed $boardingConfig = null;

    /** @var int */
    public ?int $timeout = null;

    /** @var bool */
    public ?bool $validated = null;

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

        $this->validated = true;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        if (!empty($this->gatewayConfig)) {
            $this->gatewayConfig->configureContainer($services);
        }

        if (!empty($this->deviceConnectionConfig)) {
            $this->deviceConnectionConfig->configureContainer($services);
        }
    }
}
