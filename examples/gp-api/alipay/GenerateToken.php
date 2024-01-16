<?php

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;

class GenerateToken
{
    const APP_ID = 'bvKLJsu6vYC9zxX2BpOgNK95kbboP3Uw';
    const APP_KEY = '7aH9QlA3yVFwpESQ';
    const ACCOUNT_ID = 'TRA_1366cd0db8c14fffb130ab49be84d944';

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
