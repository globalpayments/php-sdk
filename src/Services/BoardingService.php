<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\BoardingConfig;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\OnlineBoarding\BoardingApplication;
use GlobalPayments\Api\Entities\OnlineBoarding\BoardingResponse;
use GlobalPayments\Api\Entities\OnlineBoarding\MerchantInfo;
use GlobalPayments\Api\Gateways\OnlineBoardingConnector;

class BoardingService
{
    /**
     * @var BoardingConfig
     */
    public $config;
    
    /**
     * Instatiates a new object
     *
     * @param BoardingConfig $config Service config
     *
     * @return void
     */
    public function __construct(BoardingConfig $config, $configName = "default")
    {
        $this->config = $config;
        $config->validate();
        ServicesContainer::configureService($config, $configName);
        return ServicesContainer::instance()->getBoardingConnector();
    }
    
    public function newApplication()
    {
        $boardingApplication = new BoardingApplication();
        $merchantInfo = new MerchantInfo();
        $merchantInfo->affiliatePartnerId = $this->config->portal;
        $boardingApplication->merchantInfo = $merchantInfo;
        return $boardingApplication;
    }
    
    public function submitApplication($invitation, BoardingApplication $application = null, $configName = "default")
    {
        $conn = ServicesContainer::instance()->getBoardingConnector();
        return $conn->sendApplication($invitation, $application);
    }
}
