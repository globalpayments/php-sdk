<?php

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;

class GenerateToken
{
    const APP_ID = 'QzFNaCAVCSH4tELLYz5iReERAJ3mqHu7';
    const APP_KEY = '0QCyAwox3nRufZhX';
    const ACCOUNT_ID = 'TRA_c7fdc03bc9354fd3b674dddb22583553';

    private static $instance = null;
    private string $accessToken;

    private function __construct()
    {
        $config = new GpApiConfig();
        $config->appId = self::APP_ID;
        $config->appKey = self::APP_KEY;
        $config->channel = Channel::CardNotPresent;
        $config->permissions = ["PMT_POST_Create_Single","ACC_GET_Single"];

        $accessTokenInfo = GpApiService::generateTransactionKey($config);
        $this->accessToken = $accessTokenInfo->accessToken;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new GenerateToken();
        }

        return self::$instance;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
