<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Gateways\IPaymentGateway;
use GlobalPayments\Api\Gateways\IRecurringService;
use GlobalPayments\Api\Gateways\PayPlanConnector;
use GlobalPayments\Api\Gateways\PorticoConnector;
use GlobalPayments\Api\Gateways\RealexConnector;
use GlobalPayments\Api\Gateways\OnlineBoardingConnector;

class ServicesContainer
{
    /** @var IPaymentGateway */
    private $gateway;
    /** @var IRecurringService */
    private $recurring;
    /** @var ServicesContainer */
    private static $instance;
    /** @var OnlineBoardingConnector */
    private $boarding;

    /**
     * ServicesContainer constructor.
     *
     * @param IGateway $gateway
     *
     * @return
     */
    public function __construct(
        IPaymentGateway $gateway = null,
        IRecurringService $recurring = null,
        OnlineBoardingConnector $boarding = null
    ) {
        $this->gateway = $gateway;
        $this->recurring = $recurring;
        $this->boarding = $boarding;
    }

    /**
     * Gets the current `ServicesContainer` instance
     *
     * @return ServicesContainer
     */
    public static function instance()
    {
        if (static::$instance == null) {
            static::$instance = new static(null);
        }
        return static::$instance;
    }

    /**
     * Configures the `ServicesContainer` singleton
     *
     * @param ServicesConfig $config
     *
     * @return void
     */
    public static function configure(ServicesConfig $config)
    {
        $config->validate();

        $gateway = null;
        if (isset($config->merchantId) && !empty($config->merchantId)) {
            $gateway = new RealexConnector();
            $gateway->merchantId = $config->merchantId;
            $gateway->sharedSecret = $config->sharedSecret;
            $gateway->accountId = $config->accountId;
            $gateway->channel = $config->channel;
            $gateway->rebatePassword = $config->rebatePassword;
            $gateway->refundPassword = $config->refundPassword;
            $gateway->timeout = $config->timeout;
            $gateway->serviceUrl = $config->serviceUrl;
            $gateway->hostedPaymentConfig = $config->hostedPaymentConfig;
            static::$instance = new static($gateway, $gateway);
        } else {
            $gateway = new PorticoConnector();
            $gateway->siteId = $config->siteId;
            $gateway->licenseId = $config->licenseId;
            $gateway->deviceId = $config->deviceId;
            $gateway->username = $config->username;
            $gateway->password = $config->password;
            $gateway->secretApiKey = $config->secretApiKey;
            $gateway->developerId = $config->developerId;
            $gateway->versionNumber = $config->versionNumber;
            $gateway->timeout = $config->timeout;
            $gateway->serviceUrl = $config->serviceUrl . '/Hps.Exchange.PosGateway/PosGatewayService.asmx';

            $payplanEndPoint = (strpos(strtolower($config->secretApiKey), '_cert_') > 0) ?
                                '/Portico.PayPlan.v2/':
                                '/PayPlan.v2/';
            
            $recurring = new PayPlanConnector();
            $recurring->siteId = $config->siteId;
            $recurring->licenseId = $config->licenseId;
            $recurring->deviceId = $config->deviceId;
            $recurring->username = $config->username;
            $recurring->password = $config->password;
            $recurring->secretApiKey = $config->secretApiKey;
            $recurring->developerId = $config->developerId;
            $recurring->versionNumber = $config->versionNumber;
            $recurring->timeout = $config->timeout;
            $recurring->serviceUrl = $config->serviceUrl . $payplanEndPoint;

            static::$instance = new static($gateway, $recurring);
        }
    }
    
    public static function configureService($config, $configName = "default")
    {
        $boarding = new OnlineBoardingConnector();
        $boarding->portal = $config->portal;
        static::$instance = new static(null, null, $boarding);
    }
    
    /**
     * Gets the configured gateway connector
     *
     * @return BoardingConnector
     */
    public function getBoardingConnector()
    {
        return $this->boarding;
    }

    /**
     * Gets the configured gateway connector
     *
     * @return IPaymentGateway
     */
    public function getClient()
    {
        return $this->gateway;
    }

    /**
     * Gets the configured recurring gateway connector
     *
     * @return IRecurringService
     */
    public function getRecurringClient()
    {
        return $this->recurring;
    }
}
