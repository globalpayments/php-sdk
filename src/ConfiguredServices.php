<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Gateways\IPaymentGateway;
use GlobalPayments\Api\Gateways\IRecurringService;
use GlobalPayments\Api\Gateways\ISecure3dProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;

class ConfiguredServices
{
    private $secure3dProviders;

    /** @var IPaymentGateway */
    public $gatewayConnector;

    /** @var IRecurringService */
    public $recurringConnector;

    /** @var IReportingService */
    public $reportingService;

    /** @var IDeviceInterface */
    public $deviceInterface;

    /** @var DeviceController */
    public $deviceController;

    /** @var OnlineBoardingConnector */
    public $boardingConnector;

    /** @var TableServiceConnector */
    public $tableServiceConnector;

    /** @var PayrollConnector */
    public $payrollConnector;

    public function __construct()
    {
        $this->secure3dProviders = array();
    }

    protected function getSecure3dProvider(Secure3dVersion $version)
    {
        if (in_array($version, $this->secure3dProviders)) {
            return $this->secure3dProviders[$version];
        } elseif ($version == Secure3dVersion::ANY) {
            $provider = $this->secure3dProviders[Secure3dVersion::TWO];
            if ($provider == null) {
                $provider = $this->secure3dProviders[Secure3dVersion::ONE];
            }
            return $provider;
        } else {
            return null;
        }
    }

    protected function setSecure3dProvider(Secure3dVersion $version, ISecure3dProvider $provider)
    {
        $this->secure3dProviders[$version] = $provider;
    }
}
