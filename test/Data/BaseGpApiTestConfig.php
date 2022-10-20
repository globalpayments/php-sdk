<?php

namespace GlobalPayments\Api\Tests\Data;

use GlobalPayments\Api\Entities\CustomWebProxy;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;

class BaseGpApiTestConfig
{

    public static $appId = '4gPqnGBkppGYvoE5UX9EWQlotTxGUDbs';
    public static $appKey = 'FQyJA5VuEQfcji2M';

    private static $logEnabled = true;
    private static $dynamicHeaderEnabled = false;
    private static $permissionsEnabled = false;
    private static $webProxyEnabled = false;

    public static function gpApiSetupConfig($channel): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = self::$appId;
        $config->appKey = self::$appKey;
        $config->environment = Environment::TEST;
        $config->channel = $channel;
        $config->country = 'US';

        $config->challengeNotificationUrl = "https://ensi808o85za.x.pipedream.net/";
        $config->methodNotificationUrl = "https://ensi808o85za.x.pipedream.net/";
        $config->merchantContactUrl = "https://ensi808o85za.x.pipedream.net/";

        if (self::$logEnabled) {
            $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        }

        if (self::$dynamicHeaderEnabled) {
            $config->dynamicHeaders = [
                'x-gp-platform' => 'prestashop;version=1.7.2',
                'x-gp-extension' => 'coccinet;version=2.4.1',
            ];
        }

        if (self::$permissionsEnabled) {
            $config->permissions = ['TRN_POST_Authorize'];
        }

        if (self::$webProxyEnabled) {
            $config->webProxy = new CustomWebProxy('127.0.0.1:8866');
        }

        return $config;
    }

}