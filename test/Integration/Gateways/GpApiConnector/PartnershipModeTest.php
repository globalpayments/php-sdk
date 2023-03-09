<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\ThreeDSecureAcsClient;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class PartnershipModeTest extends TestCase
{
    /** @var CreditCardData */
    private $card;
    /** @var string */
    private $currency;
    /** @var string */
    private $accessToken;
    /** @var GpApiConfig */
    private $baseConfig;
    private $amount;

    /** @var Address */
    private $shippingAddress;

    /** @var BrowserData */
    private $browserData;

    private $merchantId = 'MER_7e3e2c7df34f42819b3edee31022ee3f';

    public function setup() : void
    {
        $this->baseConfig = $this->setUpConfig();
        /** @var \GlobalPayments\Api\Entities\GpApi\AccessTokenInfo $accessTokenInfo */
        $accessTokenInfo = GpApiService::generateTransactionKey($this->baseConfig);
        $this->accessToken = $accessTokenInfo->accessToken;

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";
        $this->currency = 'EUR';
        $this->amount = '10.01';

        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = "Apartment 852";
        $this->shippingAddress->streetAddress2 = "Complex 741";
        $this->shippingAddress->streetAddress3 = "no";
        $this->shippingAddress->city = "Chicago";
        $this->shippingAddress->postalCode = "5001";
        $this->shippingAddress->state = "IL";
        $this->shippingAddress->countryCode = "840";

        $this->browserData = new BrowserData();
        $this->browserData->acceptHeader = "text/html,application/xhtml+xml,application/xml;q=9,image/webp,img/apng,*/*;q=0.8";
        $this->browserData->colorDepth = ColorDepth::TWENTY_FOUR_BITS;
        $this->browserData->ipAddress = "123.123.123.123";
        $this->browserData->javaEnabled = true;
        $this->browserData->javaScriptEnabled = true;
        $this->browserData->language = "en";
        $this->browserData->screenHeight = 1080;
        $this->browserData->screenWidth = 1920;
        $this->browserData->challengWindowSize = ChallengeWindowSize::WINDOWED_600X400;
        $this->browserData->timeZone = "0";
        $this->browserData->userAgent = "Mozilla/5.0 (Windows NT 6.1; Win64, x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36";
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        BaseGpApiTestConfig::$appId = 'zKxybfLqH7vAOtBQrApxD5AUpS3ITaPz';
        BaseGpApiTestConfig::$appKey = 'GAMlgEojm6hxZTLI';

        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testCreditSaleWithPartnerMode()
    {
        $merchants = ['MER_7e3e2c7df34f42819b3edee31022ee3f'];

        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";
        foreach ($merchants as $merchantId) {
            $config = clone($this->baseConfig);
            $config->merchantId = $merchantId;
            $config->accessTokenInfo = new AccessTokenInfo();
            $config->accessTokenInfo->accessToken = $this->accessToken;
            $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
            $configName = 'config_' . $merchantId;
            ServicesContainer::configureService($config, $configName);

            $response = $this->card->charge(69)
                ->withCurrency($this->currency)
                ->withAddress($address)
                ->execute($configName);

            $this->assertNotNull($response);
            $this->assertEquals('SUCCESS', $response->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
            unset($config);
        }
    }

    public function testCreditSaleRefundWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $response = $this->card->charge(11)
            ->withCurrency($this->currency)
            ->withAddress($this->shippingAddress)
            ->execute($configName);

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $refundResponse = $response->refund(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($refundResponse);
        $this->assertEquals('SUCCESS', $refundResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $refundResponse->responseMessage);
        unset($config);
    }

    public function testCreditRefundWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $refundResponse = $this->card->refund(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($refundResponse);
        $this->assertEquals('SUCCESS', $refundResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $refundResponse->responseMessage);
        unset($config);
    }

    public function testCreditAuthAndCaptureWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $captureResponse = $authResponse->capture(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($captureResponse);
        $this->assertEquals('SUCCESS', $captureResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureResponse->responseMessage);

        unset($config);
    }

    public function testCreditAuthAndReverseWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $reverseResponse = $authResponse->reverse(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('SUCCESS', $reverseResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverseResponse->responseMessage);
        unset($config);
    }

    public function testCreditReAuthWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $reverseResponse = $authResponse->reverse(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('SUCCESS', $reverseResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverseResponse->responseMessage);

        $reAuthResponse = $reverseResponse->reauthorized(11)
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($reAuthResponse);
        $this->assertEquals('SUCCESS', $reAuthResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reAuthResponse->responseMessage);
        unset($config);
    }

    public function testFullCycle3DSChallenge_v2_PartnerMode()
    {
        $this->card->number = '4222000001227408';

        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $config->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->merchantContactUrl = 'https://enp4qhvjseljg.x.pipedream.net/';

        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute($configName);

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute($configName);

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($config->getGatewayProvider());
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->execute($configName);
        $this->card->threeDSecure = $secureEcom;

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute($configName);
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testVerifyTokenizedPaymentMethodWithPartnerMode()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';

        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $response = $this->card->tokenize()->execute($configName);
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;

        $response = $tokenizedCard->verify()
            ->withCurrency($this->currency)
            ->execute($configName);

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCreditSaleWithPartnerMode_WrongMerchant()
    {
        $merchantId = 'MER_' . GenerationUtils::getGuid();

        $config = clone($this->baseConfig);
        $config->merchantId = $merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $configName = 'config_' . $merchantId;
        ServicesContainer::configureService($config, $configName);

        $exceptionCaught = false;
        try {
            $this->card->charge(5)
                ->withCurrency($this->currency)
                ->execute($configName);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40042', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
        unset($config);
    }

    public function testCreditSaleWithPartnerMode_MisConfiguration()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->transactionProcessingAccountName = 'tokenization';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $exceptionCaught = false;
        try {
            $this->card->charge(5)
                ->withCurrency($this->currency)
                ->execute($configName);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40041', $e->responseCode);
            $this->assertStringContainsString(' INVALID_REQUEST_DATA - Merchant configuration does not exist', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
        unset($config);
    }

    public function testCreditSaleWithPartnerMode_MissingAccountName()
    {
        $config = clone($this->baseConfig);
        $config->merchantId = $this->merchantId;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->accessToken = $this->accessToken;
        $config->accessTokenInfo->tokenizationAccountName = 'tokenization';
        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $exceptionCaught = false;
        try {
            $this->card->charge(5)
                ->withCurrency($this->currency)
                ->execute($configName);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40007', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Request expects the following conditionally mandatory fields account_id, account_name.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
        unset($config);
    }
}