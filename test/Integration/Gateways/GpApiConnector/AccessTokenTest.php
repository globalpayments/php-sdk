<?php


namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    private $environment = Environment::TEST;
    private $appId = "i872l4VgZRtSrykvSn8Lkah8RE1jihvT";
    private $appKey = "9pArW2uWoA8enxKc";
    /**
     * @var GpApiConfig $config
     */
    private $config;

    public function setup()
    {
        $this->setUpConfig();
    }

    public function testGenerateAccessToken()
    {
        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);

        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testCreateAccessTokenWithSpecific_SecondsToExpire()
    {
        $this->config->secondsToExpire = 200;
        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);

        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testCreateAccessTokenWithSpecific_IntervalToExpire()
    {
        $this->config->intervalToExpire = '1_HOUR';
        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);

        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testCreateAccessTokenWithSpecificExpiredDate()
    {
        $this->config->secondsToExpire = 200;
        $this->config->intervalToExpire = 'WEEK';
        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);

        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testGenerateAccessTokenWrongAppId()
    {
        $this->config->appId = $this->appId . 'a';
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - App credentials not recognized', $e->getMessage());
        }
    }

    public function testGenerateAccessTokenWrongAppKey()
    {
        $this->config->appKey = $this->appKey . 'a';
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Credentials not recognized to create access token.', $e->getMessage());
        }
    }

    private function assertAccessTokenResponse(AccessTokenInfo $accessTokenInfo)
    {
        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->accessToken);

        $this->assertEquals("Settlement Reporting", $accessTokenInfo->dataAccountName);
        $this->assertEquals("Dispute Management", $accessTokenInfo->disputeManagementAccountName);
        $this->assertEquals("Tokenization", $accessTokenInfo->tokenizationAccountName);
        $this->assertEquals("Transaction_Processing", $accessTokenInfo->transactionProcessingAccountName);
    }

    public function setUpConfig()
    {
        $this->config = new GpApiConfig();
        $this->config->appId = $this->appId;
        $this->config->appKey = $this->appKey;
        $this->config->environment = $this->environment;
    }
}