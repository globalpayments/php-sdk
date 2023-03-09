<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\IntervalToExpire;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /**
     * @var GpApiConfig $config
     */
    private $config;

    public function setUp(): void
    {
        $this->setUpConfig();
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testGenerateAccessToken()
    {
        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);
        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testGenerateAccessToken_WithPermissions()
    {
        $this->config->permissions = ["PMT_POST_Create", "TRN_POST_Authorize", "DIS_POST_Accept", "TRN_GET_List_Funded", "RAS_POST_Create"];

        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);
        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testGenerateAccessToken_WithLimitedPermissions()
    {
        $this->config->permissions = ["PMT_POST_Create", "TRN_POST_Authorize"];

        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);

        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->accessToken);
        $this->assertEquals("tokenization", $accessTokenInfo->tokenizationAccountName);
        $this->assertEquals("transaction_processing", $accessTokenInfo->transactionProcessingAccountName);
        $this->assertNull($accessTokenInfo->dataAccountName);
        $this->assertNull($accessTokenInfo->disputeManagementAccountName);
    }

    public function testGenerateAccessToken_WithWrongPermissions()
    {
        $this->config->permissions = ["TEST_1", "TEST_2"];

        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40119', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Invalid permissions [ TEST_1,TEST_2 ] provided in the input field - permissions', $e->getMessage());
        }
    }

    public function testCreateAccessTokenWithSpecific_SecondsToExpire()
    {
        $this->config->secondsToExpire = 200;

        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);
        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testCreateAccessTokenWithSpecific_IntervalToExpire()
    {
        $this->config->intervalToExpire = IntervalToExpire::ONE_HOUR;

        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);
        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testCreateAccessTokenWithSpecificExpiredDate()
    {
        $this->config->secondsToExpire = 200;
        $this->config->intervalToExpire = IntervalToExpire::WEEK;

        $accessTokenInfo = GpApiService::generateTransactionKey($this->config);
        $this->assertAccessTokenResponse($accessTokenInfo);
    }

    public function testGenerateAccessTokenWrongAppId()
    {
        $this->config->appId = BaseGpApiTestConfig::$appId . 'a';
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - App credentials not recognized', $e->getMessage());
        }
    }

    public function testGenerateAccessTokenWrongAppKey()
    {
        $this->config->appKey = BaseGpApiTestConfig::$appKey . 'a';
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40004', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Credentials not recognized to create access token.', $e->getMessage());
        }
    }

    public function testCreateAccessTokenWithMaximum_SecondsToExpire()
    {
        $this->config->secondsToExpire = 604801;
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40213', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - seconds_to_expire contains unexpected data', $e->getMessage());
        }
    }

    public function testCreateAccessTokenWithInvalid_SecondsToExpire()
    {
        $this->config->secondsToExpire = 10;
        try {
            GpApiService::generateTransactionKey($this->config);
        } catch (GatewayException $e) {
            $this->assertEquals('40213', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - seconds_to_expire contains unexpected data', $e->getMessage());
        }
    }

    public function testUseExpiredAccessToken()
    {
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->accessToken = "r1SzGAx2K9z5FNiMHkrapfRh8BC8";
        $accessTokenInfo->dataAccountName = "Settlement Reporting";
        $accessTokenInfo->disputeManagementAccountName = "Dispute Management";
        $accessTokenInfo->tokenizationAccountName = "Tokenization";
        $accessTokenInfo->transactionProcessingAccountName = "Transaction_Processing";
        $config = new GpApiConfig();
        $config->accessTokenInfo = $accessTokenInfo;
        $config->channel = Channel::CardNotPresent;

        ServicesContainer::configureService($config);

        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = "05";
        $card->expYear = "2025";
        $card->cvn = "852";

        try {
            $card->verify()->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40001', $e->responseCode);
            $this->assertEquals('Status Code: NOT_AUTHENTICATED - Invalid access token', $e->getMessage());
        }
    }

    public function testUseInvalidAccessToken()
    {
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->accessToken = GenerationUtils::getGuid();
        $accessTokenInfo->dataAccountName = "Settlement Reporting";
        $accessTokenInfo->disputeManagementAccountName = "Dispute Management";
        $accessTokenInfo->tokenizationAccountName = "Tokenization";
        $accessTokenInfo->transactionProcessingAccountName = "Transaction_Processing";
        $config = new GpApiConfig();
        $config->accessTokenInfo = $accessTokenInfo;
        $config->channel = Channel::CardNotPresent;

        ServicesContainer::configureService($config);

        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = "05";
        $card->expYear = "2025";
        $card->cvn = "852";

        try {
            $card->verify()->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40001', $e->responseCode);
            $this->assertEquals('Status Code: NOT_AUTHENTICATED - Invalid access token', $e->getMessage());
        }
    }

    public function testUseOnlyAccessToken()
    {
        $config = $this->setUpConfig();
        $accessTokenInfo = GpApiService::generateTransactionKey($config);

        $newConfig = $this->setUpConfig();
        $newConfig->accessTokenInfo = $accessTokenInfo;

        ServicesContainer::configureService($newConfig, "configName");

        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = "05";
        $card->expYear = "2025";
        $card->cvn = "852";

        $response = $card->verify()
            ->withCurrency('EUR')
            ->execute("configName");

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    private function assertAccessTokenResponse(AccessTokenInfo $accessTokenInfo)
    {
        $this->assertNotNull($accessTokenInfo);
        $this->assertNotNull($accessTokenInfo->accessToken);

        $this->assertEquals("settlement_reporting", $accessTokenInfo->dataAccountName);
        $this->assertEquals("dispute_management", $accessTokenInfo->disputeManagementAccountName);
        $this->assertEquals("tokenization", $accessTokenInfo->tokenizationAccountName);
        $this->assertEquals("transaction_processing", $accessTokenInfo->transactionProcessingAccountName);
        $this->assertNotNull($accessTokenInfo->transactionProcessingAccountID);
        $this->assertNotNull($accessTokenInfo->tokenizationAccountID);
        $this->assertNotNull($accessTokenInfo->riskAssessmentAccountID);
        $this->assertNotNull($accessTokenInfo->disputeManagementAccountID);
        $this->assertNotNull($accessTokenInfo->dataAccountID);
    }

    public function setUpConfig()
    {
        $this->config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);

        return $this->config;
    }
}