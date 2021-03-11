<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Gateways\IPaymentGateway;
use GlobalPayments\Api\Gateways\IRecurringService;
use GlobalPayments\Api\Gateways\ISecure3dProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;

class ConfiguredServices
{
    private $secure3dProviders;
    
    /** @var IPayFacProvider  */
    private $payFacProvider;

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

    public function getSecure3dProvider($version)
    {
        if (array_key_exists($version, $this->secure3dProviders)) {
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

    public function setSecure3dProvider($version, $provider)
    {
        $this->secure3dProviders[$version] = $provider;
    }
    
    /**
     * @return void
     */
    public function setPayFacProvider($provider)
    {
        $this->payFacProvider = $provider;
    }
    
    /**
     * @return IPayFacProvider
     */
    public function getPayFacProvider()
    {
        $provider = $this->payFacProvider;
        if ($provider != null) {
            return $provider;
        }
        return null;
    }
}
