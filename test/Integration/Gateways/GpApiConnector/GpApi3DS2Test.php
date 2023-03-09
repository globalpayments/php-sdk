<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeRequestIndicator;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\DeliveryTimeFrame;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\SdkInterface;
use GlobalPayments\Api\Entities\Enums\SdkUiType;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ShippingMethod;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialReason;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Data\GpApi3DSTestCards;
use GlobalPayments\Api\Tests\Integration\Gateways\ThreeDSecureAcsClient;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GpApi3DS2Test extends TestCase
{
    /**
     * @var Address
     */
    private $shippingAddress;

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

    /** @var string|float */
    private $amount;

    /**
     * @var CreditCardData
     */
    private $card;

    public function setup(): void
    {
        $config = $this->setUpConfig();
        ServicesContainer::configureService($config);
        $this->gatewayProvider = $config->getGatewayProvider();
        $this->currency = 'GBP';
        $this->amount = '10.01';

        $this->card = new CreditCardData();
        $this->card->number = GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_1;
        $this->card->expMonth = '12';
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";

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
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    /**
     * Frictionless scenario
     *
     * @throws ApiException
     */
    public function testFullCycle_v2_Frictionless()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $this->assertNotEmpty($secureEcom->issuerAcsUrl);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);

        $this->card->threeDSecure = $secureEcom;

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testFullCycle_v2_FrictionlessFailed()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_FAILED_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $this->assertNotEmpty($secureEcom->issuerAcsUrl);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::NOT_AUTHENTICATED, $initAuth->status);
        $this->assertNotEquals('YES', $initAuth->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::NOT_AUTHENTICATED, $secureEcom->status);
        $this->card->threeDSecure = $secureEcom;

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testFullCycle_v2_WithCardTokenization()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);

        $initAuth = Secure3dService::initiateAuthentication($tokenizedCard, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $tokenizedCard->threeDSecure = $secureEcom;

        $response = $tokenizedCard->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Challenge scenario
     *
     * @throws ApiException
     */
    public function testFullCycle_CardHolderEnrolled_ChallengeRequired_v2()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->card->threeDSecure = $secureEcom;
        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Tests for 3DS v2 Challenge - Check Availability
     */
    public function testCardHolderEnrolled_ChallengeRequired_v22()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withIdempotencyKey($idempotencyKey)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $exceptionCaught = false;
        try {
            Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_WithTokenizedCard()
    {
        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_AllPreferenceValues()
    {
        $challengeRequestIndicator = new ChallengeRequestIndicator();
        $reflectionClass = new ReflectionClass($challengeRequestIndicator);
        foreach ($reflectionClass->getConstants() as $value) {

            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withChallengeRequestIndicator($value)
                ->execute();

            $this->assertCheckEnrollment3DSV2($secureEcom);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_StoredCredentials()
    {
        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::MERCHANT;
        $storeCredentials->type = StoredCredentialType::INSTALLMENT;
        $storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->withStoredCredential($storeCredentials)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_AllSources()
    {
        $source = array("BROWSER", "MERCHANT_INITIATED", "MOBILE_SDK", "STORED_RECURRING");
        foreach ($source as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withAuthenticationSource($value)
                ->execute();
        }

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_WithNullPaymentMethod()
    {
        $exceptionCaught = false;
        try {
            Secure3dService::checkEnrollment($this->card)
                ->withPaymentMethod(null)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40007', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Request expects the following conditionally mandatory fields number,expiry_month,expiry_year.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * Tests for 3DS v2 Frictionless - Check Availability
     */
    public function testCardHolderEnrolled_Frictionless_v2()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_Frictionless_v2_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withIdempotencyKey($idempotencyKey)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $exceptionCaught = false;
        try {
            Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardHolderEnrolled_Frictionless_v2_WithTokenizedCard()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_Frictionless_v2_AllPreferenceValues()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $challengeRequestIndicator = new ChallengeRequestIndicator();
        $reflectionClass = new ReflectionClass($challengeRequestIndicator);
        foreach ($reflectionClass->getConstants() as $value) {

            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withChallengeRequestIndicator($value)
                ->execute();

            $this->assertCheckEnrollment3DSV2($secureEcom);
        }
    }

    public function testCardHolderEnrolled_Frictionless_v2_StoredCredentials()
    {
        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::MERCHANT;
        $storeCredentials->type = StoredCredentialType::INSTALLMENT;
        $storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->withStoredCredential($storeCredentials)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    public function testCardHolderEnrolled_Frictionless_v2_AllSources()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $source = array("BROWSER", "MERCHANT_INITIATED", "MOBILE_SDK", "STORED_RECURRING");
        foreach ($source as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withAuthenticationSource($value)
                ->execute();
        }

        $this->assertCheckEnrollment3DSV2($secureEcom);
    }

    /**
     * Tests for 3DS v2 Challenge Required - Obtain Result
     */
    //TODO - asserts for POST result
    public function testCardHolderChallengeRequired_PostResult()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('05', $secureEcom->eci);
        $this->assertEquals('2.1.0', $secureEcom->messageVersion);
        $this->assertNotNull($secureEcom->acsTransactionId);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertNotNull($secureEcom->directoryServerTransactionId);
    }

    public function testCardHolderChallengeRequired_PostResult_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('05', $secureEcom->eci);
        $this->assertEquals('2.1.0', $secureEcom->messageVersion);
        $this->assertNotNull($secureEcom->acsTransactionId);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertNotNull($secureEcom->directoryServerTransactionId);

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($secureEcom->serverTransactionId)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * Tests for 3DS v2 Frictionless - Obtain Result
     */
    //TODO - asserts for POST result
    public function testCardHolderFrictionless_PostResult()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('05', $secureEcom->eci);
        $this->assertEquals('2.2.0', $secureEcom->messageVersion);
        $this->assertNotNull($secureEcom->acsTransactionId);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertNotNull($secureEcom->directoryServerTransactionId);
        $this->assertEmpty($secureEcom->payerAuthenticationRequest);
    }

    public function testCardHolderFrictionless_PostResult_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('05', $secureEcom->eci);
        $this->assertEquals('2.2.0', $secureEcom->messageVersion);
        $this->assertNotNull($secureEcom->acsTransactionId);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertNotNull($secureEcom->directoryServerTransactionId);
        $this->assertEmpty($secureEcom->payerAuthenticationRequest);

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($secureEcom->serverTransactionId)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardHolderFrictionless_PostResult_NonExistentId()
    {
        $transactionId = "AUT_" . GenerationUtils::getGuid();

        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($transactionId)
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - Authentication ' . $transactionId .
                ' not found at this location.', $e->getMessage());
        }
    }

    /**
     * Tests for 3DS v2 Challenge - Initiate
     */
    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_With_IdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);

        $exceptionCaught = false;
        try {
            Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_TokenizedCard()
    {
        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($tokenizedCard, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_MethodUrlSetNo()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_MethodUrlSetUnavailable()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::UNAVAILABLE)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_Without_ShippingAddress()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_With_GiftCard()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withGiftCardAmount(2)
            ->withGiftCardCount(1)
            ->withGiftCardCurrency($this->currency)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_With_ShippingMethod()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withShippingMethod(ShippingMethod::DIGITAL_GOODS)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_With_DeliveryEmail()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withDeliveryEmail('james.mason@example.com')
            ->withDeliveryTimeFrame(DeliveryTimeFrame::SAME_DAY)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_AllPreferenceValues()
    {
        $challengeRequestIndicator = new ChallengeRequestIndicator();
        $reflectionClass = new ReflectionClass($challengeRequestIndicator);
        foreach ($reflectionClass->getConstants() as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->execute();

            $this->assertCheckEnrollment3DSV2($secureEcom);
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withChallengeRequestIndicator($value)
                ->withBrowserData($this->browserData)
                ->execute();

            $this->assertInitiate3DSV2($initAuth);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_AllSourceValues()
    {
        $this->markTestSkipped('MERCHANT_INITIATED and STORED_RECURRING failing, issue raised with UCP team.');
        $source = [
            AuthenticationSource::BROWSER,
            AuthenticationSource::MERCHANT_INITIATED,
            AuthenticationSource::STORED_RECURRING
        ];

        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::PAYER;
        $storeCredentials->type = StoredCredentialType::SPLIT_OR_DELAYED_SHIPMENT;
        $storeCredentials->sequence = StoredCredentialSequence::FIRST;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        foreach ($source as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withAuthenticationSource($value)
                ->withStoredCredential($storeCredentials)
                ->execute();

            $this->assertCheckEnrollment3DSV2($secureEcom);

            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource($value)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withStoredCredential($storeCredentials)
                ->execute();

            $this->assertInitiate3DSV2($initAuth);
        }
    }

    public function testCardHolderEnrolled_Frictionless_v2_Initiate()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withShippingMethod(ShippingMethod::DIGITAL_GOODS)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertNotNull($initAuth->acsTransactionId);
        $this->assertEquals("05", $initAuth->eci);
        $this->assertEquals("2.2.0", $initAuth->messageVersion);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_Without_PaymentMethod()
    {
        $card = new CreditCardData();
        $secureEcom = new ThreeDSecure();
        $secureEcom->serverTransactionId = "AUT_" . GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            Secure3dService::initiateAuthentication($card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields number', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_Initiate_NonExistentId()
    {
        $transactionId = "AUT_" . GenerationUtils::getGuid();
        $secureEcom = new ThreeDSecure();
        $secureEcom->serverTransactionId = $transactionId;

        $exceptionCaught = false;
        try {
            Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - Authentication ' . $transactionId .
                ' not found at this location.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testChallengeRequired_v2_Initiate_MobileSDK()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $mobileData = new \GlobalPayments\Api\Entities\MobileData();
        $mobileData->encodedData = 'ew0KCSJEViI6ICIxLjAiLA0KCSJERCI6IHsNCgkJIkMwMDEiOiAiQW5kcm9pZCIsDQoJCSJDMDAyIjogIkhUQyBPbmVfTTgiLA0KCQkiQzAwNCI6ICI1LjAuMSIsDQoJCSJDMDA1IjogImVuX1VTIiwNCgkJIkMwMDYiOiAiRWFzdGVybiBTdGFuZGFyZCBUaW1lIiwNCgkJIkMwMDciOiAiMDY3OTc5MDMtZmI2MS00MWVkLTk0YzItNGQyYjc0ZTI3ZDE4IiwNCgkJIkMwMDkiOiAiSm9obidzIEFuZHJvaWQgRGV2aWNlIg0KCX0sDQoJIkRQTkEiOiB7DQoJCSJDMDEwIjogIlJFMDEiLA0KCQkiQzAxMSI6ICJSRTAzIg0KCX0sDQoJIlNXIjogWyJTVzAxIiwgIlNXMDQiXQ0KfQ0K';
        $mobileData->applicationReference = 'f283b3ec-27da-42a1-acea-f3f70e75bbdc';
        $mobileData->sdkInterface = SdkInterface::BROWSER;
        $mobileData->sdkUiTypes = [SdkUiType::HTML_OTHER];
        $mobileData->ephemeralPublicKey = '{
            "kty": "EC",
            "crv": "P-256",
            "x": "WWcpTjbOqiu_1aODllw5rYTq5oLXE_T0huCPjMIRbkI",
            "y": "Wz_7anIeadV8SJZUfr4drwjzuWoUbOsHp5GdRZBAAiw"
        }';
        $mobileData->maximumTimeout = 50;
        $mobileData->referenceNumber = '3DS_LOA_SDK_PPFU_020100_00007';
        $mobileData->sdkTransReference = 'b2385523-a66c-4907-ac3c-91848e8c0067';

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::MOBILE_SDK)
            ->withMobileData($mobileData)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
        $this->assertEquals('HTML', $initAuth->acsInterface);
        $this->assertEquals(SdkUiType::HTML_OTHER, $initAuth->acsUiTemplate);
    }

    public function testChallengeRequired_v2_Initiate_MobileDataAndBrowserData()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $mobileData = new \GlobalPayments\Api\Entities\MobileData();
        $mobileData->encodedData = 'ew0KCSJEViI6ICIxLjAiLA0KCSJERCI6IHsNCgkJIkMwMDEiOiAiQW5kcm9pZCIsDQoJCSJDMDAyIjogIkhUQyBPbmVfTTgiLA0KCQkiQzAwNCI6ICI1LjAuMSIsDQoJCSJDMDA1IjogImVuX1VTIiwNCgkJIkMwMDYiOiAiRWFzdGVybiBTdGFuZGFyZCBUaW1lIiwNCgkJIkMwMDciOiAiMDY3OTc5MDMtZmI2MS00MWVkLTk0YzItNGQyYjc0ZTI3ZDE4IiwNCgkJIkMwMDkiOiAiSm9obidzIEFuZHJvaWQgRGV2aWNlIg0KCX0sDQoJIkRQTkEiOiB7DQoJCSJDMDEwIjogIlJFMDEiLA0KCQkiQzAxMSI6ICJSRTAzIg0KCX0sDQoJIlNXIjogWyJTVzAxIiwgIlNXMDQiXQ0KfQ0K';
        $mobileData->applicationReference = 'f283b3ec-27da-42a1-acea-f3f70e75bbdc';
        $mobileData->sdkInterface = SdkInterface::BROWSER;
        $mobileData->sdkUiTypes = [SdkUiType::HTML_OTHER];
        $mobileData->ephemeralPublicKey = '{
            "kty": "EC",
            "crv": "P-256",
            "x": "WWcpTjbOqiu_1aODllw5rYTq5oLXE_T0huCPjMIRbkI",
            "y": "Wz_7anIeadV8SJZUfr4drwjzuWoUbOsHp5GdRZBAAiw"
        }';
        $mobileData->maximumTimeout = 50;
        $mobileData->referenceNumber = '3DS_LOA_SDK_PPFU_020100_00007';
        $mobileData->sdkTransReference = 'b2385523-a66c-4907-ac3c-91848e8c0067';

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMobileData($mobileData)
            ->withBrowserData($this->browserData)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->execute();

        $this->assertInitiate3DSV2($initAuth);
    }

    public function testChallengeRequired_v2_Initiate_With_MobileData_AndSourceBrowser()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);
        $mobileData = new \GlobalPayments\Api\Entities\MobileData();
        $mobileData->encodedData = 'ew0KCSJEViI6ICIxLjAiLA0KCSJERCI6IHsNCgkJIkMwMDEiOiAiQW5kcm9pZCIsDQoJCSJDMDAyIjogIkhUQyBPbmVfTTgiLA0KCQkiQzAwNCI6ICI1LjAuMSIsDQoJCSJDMDA1IjogImVuX1VTIiwNCgkJIkMwMDYiOiAiRWFzdGVybiBTdGFuZGFyZCBUaW1lIiwNCgkJIkMwMDciOiAiMDY3OTc5MDMtZmI2MS00MWVkLTk0YzItNGQyYjc0ZTI3ZDE4IiwNCgkJIkMwMDkiOiAiSm9obidzIEFuZHJvaWQgRGV2aWNlIg0KCX0sDQoJIkRQTkEiOiB7DQoJCSJDMDEwIjogIlJFMDEiLA0KCQkiQzAxMSI6ICJSRTAzIg0KCX0sDQoJIlNXIjogWyJTVzAxIiwgIlNXMDQiXQ0KfQ0K';
        $mobileData->applicationReference = 'f283b3ec-27da-42a1-acea-f3f70e75bbdc';
        $mobileData->sdkInterface = SdkInterface::BROWSER;
        $mobileData->sdkUiTypes = [SdkUiType::HTML_OTHER];
        $mobileData->ephemeralPublicKey = '{
            "kty": "EC",
            "crv": "P-256",
            "x": "WWcpTjbOqiu_1aODllw5rYTq5oLXE_T0huCPjMIRbkI",
            "y": "Wz_7anIeadV8SJZUfr4drwjzuWoUbOsHp5GdRZBAAiw"
        }';
        $mobileData->maximumTimeout = 50;
        $mobileData->referenceNumber = '3DS_LOA_SDK_PPFU_020100_00007';
        $mobileData->sdkTransReference = 'b2385523-a66c-4907-ac3c-91848e8c0067';

        $exceptionCaught = false;
        try {
            Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withMobileData($mobileData)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40233', $e->responseCode);
            $this->assertStringStartsWith('Status Code: INVALID_REQUEST_DATA - Required Data Element browser_data', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testChallengeRequired_v2_Initiate_With_SourceMobileSdk_WithoutMobileData()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollment3DSV2($secureEcom);

        $exceptionCaught = false;
        try {
            Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::MOBILE_SDK)
                ->withMethodUrlCompletion(MethodUrlCompletion::YES)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40233', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Required Data Element sdk_information', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function assertCheckEnrollment3DSV2(ThreeDSecure $secureEcom)
    {
        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);
        $this->assertEmpty($secureEcom->eci);
    }

    private function assertInitiate3DSV2(ThreeDSecure $initAuth)
    {
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertNotNull($initAuth->acsTransactionId);
        $this->assertEmpty($initAuth->eci);
        $this->assertEquals("2.1.0", $initAuth->messageVersion);
    }

}