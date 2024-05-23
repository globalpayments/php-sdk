<?php

namespace GlobalPayments\Api\Tests\Data;

use GlobalPayments\Api\Entities\CustomWebProxy;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;

class BaseGpApiTestConfig
{
    const APP_ID = '4gPqnGBkppGYvoE5UX9EWQlotTxGUDbs';
    const APP_KEY = 'FQyJA5VuEQfcji2M';
    const PARTNER_SOLUTION_APP_ID = 'A1feRdMmEB6m0Y1aQ65H0bDi9ZeAEB2t';
    const PARTNER_SOLUTION_APP_KEY = '5jPt1OpB6LLitgi7';

    const MITC_UPA_APP_ID = 'aCgePu6PqA8sDdkjLYgmrHs89JAXvbvO';
    const MITC_UPA_APP_KEY = 'DY0ZeWiUCHACK7dz';

    public static string $appId = self::APP_ID;
    public static string $appKey = self::APP_KEY; #gitleaks:allow

    private static bool $logEnabled = true;
    private static bool $dynamicHeaderEnabled = false;
    private static bool $permissionsEnabled = false;
    private static bool $webProxyEnabled = false;

    public static function gpApiSetupConfig($channel): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = self::$appId;
        $config->appKey = self::$appKey;
        $config->environment = Environment::TEST;
        $config->channel = $channel;
        $config->country = 'US';
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->riskAssessmentAccountName = 'EOS_RiskAssessment';

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

    static function resetGpApiConfig()
    {
        ServicesContainer::removeConfiguration();
        self::$appId = self::APP_ID;
        self::$appKey = self::APP_KEY;
    }
}