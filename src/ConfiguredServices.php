<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Gateways\{
    OpenBankingProvider, 
    IPaymentGateway, 
    IRecurringService, 
    IInstallmentService
};
use GlobalPayments\Api\Gateways\Interfaces\{IFileProcessingService, IBillingProvider};
use GlobalPayments\Api\Services\FraudService;
use GlobalPayments\Api\Terminals\DeviceController;

class ConfiguredServices
{
    private $secure3dProviders;
    
    /** @var IPayFacProvider  */
    private $payFacProvider;

    /** @var OpenBankingProvider */
    private $openBankingProvider;

    /** @var IPaymentGateway */
    public $gatewayConnector;

    /** @var IRecurringService */
    public $recurringConnector;

     /** @var IInstallmentService */
     public $installmentService;

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

    /** @var FraudService */
    public $fraudService;

    public IFileProcessingService $fileProcessingService;

    /** @var IBillingProvider */
    private $billingProvider;

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
     * @return IBillingProvider
     */
    public function getBillingProvider()
    {
        return $this->billingProvider;
    }

    public function setBillingProvider(IBillingProvider $billingProvider)
    {
        $this->billingProvider = $billingProvider;
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

    public function setOpenBankingProvider($provider)
    {
        $this->openBankingProvider = $provider;
    }

    public function getOpenBankingProvider()
    {
        return $this->openBankingProvider;
    }

    public function setDeviceController(DeviceController $deviceController)
    {
        $this->deviceController= $deviceController;
        $this->deviceInterface = $deviceController->configureInterface();
    }
}
