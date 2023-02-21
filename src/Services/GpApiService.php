<?php


namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;

class GpApiService
{
    public static function generateTransactionKey(GpApiConfig $config)
    {
        $gateway = new GpApiConnector($config);
        if (empty($gateway->serviceUrl)) {
            $gateway->serviceUrl = ($config->environment == Environment::PRODUCTION) ?
                ServiceEndpoints::GP_API_PRODUCTION : ServiceEndpoints::GP_API_TEST;
        }
        $gateway->requestLogger = $config->requestLogger;
        $gateway->webProxy = $config->webProxy;
        $gateway->dynamicHeaders = $config->dynamicHeaders;

        $data = $gateway->getAccessToken();

        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->accessToken = $data->token;
        $accessTokenInfo->dataAccountName = $data->getDataAccountName();
        $accessTokenInfo->disputeManagementAccountName = $data->getDisputeManagementAccountName();
        $accessTokenInfo->transactionProcessingAccountName = $data->getTransactionProcessingAccountName();
        $accessTokenInfo->tokenizationAccountName = $data->getTokenizationAccountName();
        $accessTokenInfo->riskAssessmentAccountName = $data->getRiskAssessmentAccountName();
        $accessTokenInfo->dataAccountID = $data->getDataAccountID();
        $accessTokenInfo->disputeManagementAccountID = $data->getDisputeManagementAccountID();
        $accessTokenInfo->transactionProcessingAccountID = $data->getTransactionProcessingAccountID();
        $accessTokenInfo->tokenizationAccountID = $data->getTokenizationAccountID();
        $accessTokenInfo->riskAssessmentAccountID = $data->getRiskAssessmentAccountID();

        return $accessTokenInfo;
    }
}