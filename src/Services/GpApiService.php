<?php


namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\AccessTokenInfo;

class GpApiService
{
    public static function generateTransactionKey(
        $environment,
        $appId,
        $appKey,
        $secondsToExpire = null,
        $intervalToExpire = null
    ) {
        $config = new GpApiConfig();
        $config->setAppId($appId);
        $config->setAppKey($appKey);
        $config->setSecondsToExpire($secondsToExpire);
        $config->setIntervalToExpire($intervalToExpire);
        $config->environment = $environment;
        $config->serviceUrl = ($environment == Environment::PRODUCTION) ? ServiceEndpoints::GP_API_PRODUCTION :
            ServiceEndpoints::GP_API_TEST;
        $config->timeout = 10000;
        $accessTokenManager = new AccessTokenInfo();
        $accessTokenManager->initialize($config);
        $config->setAccessTokenInfo($accessTokenManager);
        $gateway = new GpApiConnector($config);

        return $accessTokenManager;
    }
}