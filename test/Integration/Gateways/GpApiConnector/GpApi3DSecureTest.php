<?php

use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Tests\Integration\Gateways\ThreeDSecureAcsClient;
use PHPUnit\Framework\TestCase;

class GpApi3DSecureTest extends TestCase
{
    /**
     * @var Address
     */
    private $shippingAddress;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var BrowserData
     */
    private $browserData;

    /**
     * @var string
     */
    private $gatewayProvider;

    /**
     * @var string
     */
    private $currency;

    public function setup()
    {
        $config = $this->setUpConfig();
        ServicesContainer::configureService($config);
        $this->gatewayProvider = $config->getGatewayProvider();
        $this->currency = 'GBP';

        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = "Apartment 852";
        $this->shippingAddress->streetAddress2 = "Complex 741";
        $this->shippingAddress->streetAddress3 = "no";
        $this->shippingAddress->city = "Chicago";
        $this->shippingAddress->postalCode = "5001";
        $this->shippingAddress->state = "IL";
        $this->shippingAddress->countryCode = "840";

        $this->billingAddress = new Address();
        $this->billingAddress->streetAddress1 = "Flat 456";
        $this->billingAddress->streetAddress2 = "House 789";
        $this->billingAddress->streetAddress3 = "no";
        $this->billingAddress->city = "Halifax";
        $this->billingAddress->postalCode = "W5 9HR";
        $this->billingAddress->countryCode = "826";

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

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'P3LRVjtGRGxWQQJDE345mSkEh2KfdAyg';
        $config->appKey = 'ockJr6pv6KFoGiZA';
        $config->environment = Environment::TEST;
        $config->country = 'GB';
        $config->channel = Channels::CardNotPresent;
        $config->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';

        return $config;
    }

    public function testFullCycle_v1()
    {
        $card = new CreditCardData();
        $card->number = '4012001037141112';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($card)
        ->withCurrency($this->currency)
        ->withAmount('10.01')
        ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals('CHALLENGE_REQUIRED', $secureEcom->status);
        $this->assertTrue($secureEcom->challengeMandated);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = '0';
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
            ->execute();
        $card->threeDSecure = $secureEcom;
        $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);
        $response = $card->charge(10.01)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals( TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testFullCycle_v1_WithTokenizedPaymentMethod()
    {
        $card = new CreditCardData();
        $card->number = '4012001037141112';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $response = $card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals('CHALLENGE_REQUIRED', $secureEcom->status);
        $this->assertTrue($secureEcom->challengeMandated);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = '0';
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
            ->execute();
        $tokenizedCard->threeDSecure = $secureEcom;
        $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);

        $response = $tokenizedCard->charge(10.01)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals( TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardHolderNotEnrolled_v1()
    {
        $card = new CreditCardData();
        $card->number = '4917000000000087';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($card)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals( 'NOT_ENROLLED', $secureEcom->enrolled);
        $this->assertEquals( 'NOT_ENROLLED', $secureEcom->status);
    }

    /**
     * Frictionless scenario
     *
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function testFullCycle_v2()
    {
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($card)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals('AVAILABLE', $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($card, $secureEcom)
            ->withAmount(10.01)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals('SUCCESS_AUTHENTICATED', $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();
        $card->threeDSecure = $initAuth;
        $this->assertEquals('SUCCESS_AUTHENTICATED', $secureEcom->status);

        $response = $card->charge(10.01)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals( TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Challenge scenario
     *
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function testCardHolderEnrolled_ChallengeRequired_v2()
    {
        $card = new CreditCardData();
        $card->number = '4012001038488884';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($card)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals('AVAILABLE', $secureEcom->status);
        $initAuth = Secure3dService::initiateAuthentication($card, $secureEcom)
            ->withAmount(10.01)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals('CHALLENGE_REQUIRED', $initAuth->status);
        $this->assertTrue($initAuth->challengeMandated);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->challengeValue);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $card->threeDSecure = $secureEcom;

        $this->assertEquals('SUCCESS_AUTHENTICATED', $secureEcom->status);

        $response = $card->charge(10.01)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals( TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Challenge scenario
     *
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function testChallengeRequired_GetResultFailed_v2()
    {
        $card = new CreditCardData();
        $card->number = '4012001038488884';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($card)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals('AVAILABLE', $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($card, $secureEcom)
            ->withAmount(10.01)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals('CHALLENGE_REQUIRED', $initAuth->status);
        $this->assertTrue($initAuth->challengeMandated);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->challengeValue);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $this->assertEquals('FAILED', $secureEcom->status);


    }

    /**
     * Frictionless scenario with tokenize card
     *
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public function testFullCycle_WithCardTokenization_v2()
    {
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = '12';
        $card->expYear = date('Y', strtotime('+1 year'));

        $response = $card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount('10.01')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals('ENROLLED', $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals('AVAILABLE', $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($tokenizedCard, $secureEcom)
            ->withAmount(10.01)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals('SUCCESS_AUTHENTICATED', $secureEcom->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();
        $tokenizedCard->threeDSecure = $secureEcom;
        $this->assertEquals('SUCCESS_AUTHENTICATED', $secureEcom->status);

        $response = $tokenizedCard->charge(10.01)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals( TransactionStatus::CAPTURED, $response->responseMessage);
    }
}