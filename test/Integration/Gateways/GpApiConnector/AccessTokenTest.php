<?php


namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\AccessTokenInfo;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /** @var $accessTokenInfo AccessTokenInfo */
    private $accessTokenInfo;
    private $environment = Environment::TEST;
    private $appId = "i872l4VgZRtSrykvSn8Lkah8RE1jihvT";
    private $appKey = "9pArW2uWoA8enxKc";

    public function testAccessTokenInfoAccessTokenExistence()
    {
        ServicesContainer::configureService($this->setUpConfig());
        $accessToken = $this->accessTokenInfo->generateAccessToken();

        $this->assertNotEmpty($accessToken->token);
    }

    public function testAccessTokenInfoAccountNameExistence()
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->assertNotEmpty($this->accessTokenInfo->getDataAccountName());
        $this->assertNotEmpty($this->accessTokenInfo->getDisputeManagementAccountName());
        $this->assertNotEmpty($this->accessTokenInfo->getTransactionProcessingAccountName());
        $this->assertNotEmpty($this->accessTokenInfo->getTokenizationAccountName());
    }

    public function testGenerateAccessTokenManual()
    {
        /**
         * @var AccessTokenInfo $accessTokenInfo
         */
        $accessTokenInfo = GpApiService::generateTransactionKey($this->environment, $this->appId, $this->appKey);

        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->getAccessToken());
        $this->assertNotNull($accessTokenInfo->getDataAccountName());
        $this->assertNotNull($accessTokenInfo->getDisputeManagementAccountName());
        $this->assertNotNull($accessTokenInfo->getTokenizationAccountName());
        $this->assertNotNull($accessTokenInfo->getTransactionProcessingAccountName());
    }

    public function testCreateAccessTokenWithSpecificExpiredDate()
    {
        $accessTokenInfo = GpApiService::generateTransactionKey($this->environment, $this->appId, $this->appKey, 200, 'WEEK');

        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->getAccessToken());
        $this->assertNotNull($accessTokenInfo->getDataAccountName());
        $this->assertNotNull($accessTokenInfo->getDisputeManagementAccountName());
        $this->assertNotNull($accessTokenInfo->getTokenizationAccountName());
        $this->assertNotNull($accessTokenInfo->getTransactionProcessingAccountName());
    }

    public function testCreateAccessTokenWithSpecific_SecondsToExpire()
    {
        $accessTokenInfo = GpApiService::generateTransactionKey($this->environment, $this->appId, $this->appKey, 200);

        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->getAccessToken());
        $this->assertNotNull($accessTokenInfo->getDataAccountName());
        $this->assertNotNull($accessTokenInfo->getDisputeManagementAccountName());
        $this->assertNotNull($accessTokenInfo->getTokenizationAccountName());
        $this->assertNotNull($accessTokenInfo->getTransactionProcessingAccountName());
    }

    public function testCreateAccessTokenWithSpecific_IntervalToExpire()
    {
        $accessTokenInfo = GpApiService::generateTransactionKey($this->environment, $this->appId, $this->appKey, null, "1_HOUR");

        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->getAccessToken());
        $this->assertNotNull($accessTokenInfo->getDataAccountName());
        $this->assertNotNull($accessTokenInfo->getDisputeManagementAccountName());
        $this->assertNotNull($accessTokenInfo->getTokenizationAccountName());
        $this->assertNotNull($accessTokenInfo->getTransactionProcessingAccountName());
    }

    public function testGenerateAccessTokenWrongAppId()
    {
        try {
            GpApiService::generateTransactionKey($this->environment, $this->appId . "a", $this->appKey);
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Credentials not recognized to create access token.', $e->getMessage());
        }
    }

    public function testGenerateAccessTokenWrongAppKey()
    {
        try {
            GpApiService::generateTransactionKey($this->environment, $this->appId, $this->appKey . "a");
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Credentials not recognized to create access token.', $e->getMessage());
        }
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $accessTokenManager = new \GlobalPayments\Api\Utils\AccessTokenInfo();
        //this is gpapistuff stuff
        $config->setAppId('VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF');
        $config->setAppKey('NmGM0kg92z2gA7Og');
        $config->setAccessTokenInfo($accessTokenManager);
        $this->accessTokenInfo = $accessTokenManager;

        return $config;
    }
}