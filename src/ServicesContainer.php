<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Gateways\IPaymentGateway;
use GlobalPayments\Api\Gateways\IRecurringService;
use GlobalPayments\Api\Gateways\ISecure3dProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\ServiceConfigs\ServicesConfig;

class ServicesContainer
{
    /** @var  array */
    private $secure3dProviders;
    /** @var IPaymentGateway */
    public $gatewayConnector;
    /** @var IRecurringService */
    public $recurringConnector ;
    /** @var ServicesContainer */
    private static $instance;

    private static $configurations = array();

    /**
     * ServicesContainer constructor.
     *
     * @param IGateway $gateway
     *
     * @return
     */
    public function __construct(IPaymentGateway $gateway = null, IRecurringService $recurring = null)
    {
        $this->gateway = $gateway;
        $this->recurring = $recurring;
    }

    public static function configure(ServicesConfig $config, $configName = 'default')
    {
        $config->validate();

        self::configureService($config->deviceConnectionConfig, $configName);

        self::configureService($config->tableServiceConfig, $configName);

        self::configureService($config->payrollConfig, $configName);

        self::configureService($config->gatewayConfig, $configName);
    }

    public static function configureService($config, $configName = 'default')
    {
        if ($config != null) {
            if (!($config->validated)) {
                $config->validate();
            }

            $cs = static::instance()->getConfiguration($configName);
            $config->configureContainer($cs);
            static::instance()->addConfiguration($configName, $cs);
        }
    }

    private function getConfiguration($configName)
    {
        if (array_key_exists($configName, self::$configurations)) {
            return self::$configurations[$configName];
        } else {
            return new ConfiguredServices();
        }
    }

    private function addConfiguration($configName, $configuration)
    {
        static::$configurations[$configName] = $configuration;
    }

    /**
     * Gets the current `ServicesContainer` instance
     *
     * @return ServicesContainer
     */
    public static function instance()
    {
        if (static::$instance == null) {
            static::$instance = new ServicesContainer();
        }
        return static::$instance;
    }

    /**
     * Gets the configured gateway connector
     *
     * @return IPaymentGateway
     */
    public function getClient($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->gatewayConnector;
        } else {
            throw new ApiException("The specified configuration has not been configured for gateway processing.");
        }
    }

    public function getDeviceInterface($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->deviceInterface;
        }
        
        throw new ApiException("The specified configuration has not been configured for terminal interaction.");
    }

    public function getDeviceController($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->deviceController;
        }
        
        throw new ApiException("The specified configuration has not been configured for terminal interaction.");
    }

    public function getRecurringClient($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->recurringConnector;
        }
        
        throw new ApiException("The specified configuration has not been configured for recurring processing.");
    }

    public function getTableServiceClient($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->tableServiceClient;
        }
        
        throw new ApiException("The specified configuration has not been configured for table service.");
    }

    public function getBoardingConnector($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->boardingServiceConnector;
        }
        
        return null;
    }

    public function getPayrollClient($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->payrollClient;
        }
        
        throw new ApiException("The specified configuration has not been configured for payroll.");
    }

    public function getReportingClient($configName)
    {
        if (array_key_exists($configName, static::$configurations)) {
            return static::$configurations[$configName]->reportingService;
        }
        
        throw new ApiException("The specified configuration has not been configured for reporting.");
    }

    /**
     * @return ISecure3dProvider
     */
    public function getSecure3d($configName, $version)
    {
        if (array_key_exists($configName, static::$configurations)) {
            $provider = static::$configurations[$configName]->getSecure3dProvider($version);
            if ($provider != null) {
                return $provider;
            } else {
                throw new ConfigurationException("Secure 3d is not configured for version " . $version . ".");
            }
        } else {
            throw new ConfigurationException("Secure 3d is not configured on the connector.");
        }
    }
    
    /**
     * @return IPayFacProvider
     */
    public function getPayFac($configName)
    {
        $provider = static::$configurations[$configName]->getPayFacProvider();
        if ($provider != null) {
            return $provider;
        }
        throw new ConfigurationException('payFacProvider is not configured');
    }
}
