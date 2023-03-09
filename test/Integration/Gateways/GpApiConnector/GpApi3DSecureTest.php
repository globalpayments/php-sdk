<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\ExemptStatus;
use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\OrderTransactionType;
use GlobalPayments\Api\Entities\Enums\SdkInterface;
use GlobalPayments\Api\Entities\Enums\SdkUiType;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialReason;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\MobileData;
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

class GpApi3DSecureTest extends TestCase
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

    public function testCardHolderEnrolled_ChallengeRequired_AuthenticationSuccessful_FullCycle_v1()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = '0';
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
            ->execute();
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('NO', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardHolderEnrolled_ChallengeRequired_AuthenticationSuccessful_FullCycle_v1_WithTokenizedPaymentMethod()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_ENROLLED_V1;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = '0';
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
            ->execute();
        $tokenizedCard->threeDSecure = $secureEcom;
        $this->assertEquals('SUCCESS_AUTHENTICATED', $secureEcom->status);
        $this->assertEquals('NO', $secureEcom->liabilityShift);

        $response = $tokenizedCard->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * @dataProvider ChallengeRequiredFailed3DSV1CardTests
     * @param $acsClientResultCode
     * @param $status
     * @throws ApiException
     */
    public function testCardHolderEnrolled_ChallengeRequired_AuthenticationFailed_v1($acsClientResultCode, $status)
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = $acsClientResultCode;
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
            ->execute();
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals($status, $secureEcom->status);
        $this->assertEquals("NO", $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardHolderEnrolled_ChallengeRequired_AuthenticationFailed_v1_WrongAcsValue()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->authenticationResultCode = '0';
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v1($secureEcom);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($authResponse->getMerchantData())
                ->withPayerAuthenticationResponse(GenerationUtils::getGuid())
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50020', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Unable to decompress the PARes.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }

    }

    public function testCardHolderNotEnrolled_v1()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_NOT_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentCardNotEnrolledV1($secureEcom);

        $this->card->threeDSecure = $secureEcom;

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Frictionless scenario
     *
     * @dataProvider FrictionlessSuccessful3DSV2CardTests
     * @param $cardNumber
     * @param $status
     * @throws ApiException
     */
    public function testFrictionlessFullCycle_v2($cardNumber, $status)
    {
        $this->card->number = $cardNumber;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

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
            ->withOrderTransactionType(OrderTransactionType::GOODS_SERVICE_PURCHASE)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals($status, $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals($status, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Frictionless failed scenario
     *
     * @dataProvider FrictionlessFailed3DSV2CardTests
     * @param $cardNumber
     * @param $status
     * @throws ApiException
     */
    public function testFrictionlessFullCycle_v2_Failed($cardNumber, $status)
    {
        $this->card->number = $cardNumber;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

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
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals($status, $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $liabilityShift = ($status == Secure3dStatus::SUCCESS_ATTEMPT_MADE ? 'YES' : 'NO');
        $this->assertEquals($status, $secureEcom->status);
        $this->assertEquals($liabilityShift, $secureEcom->liabilityShift);

        $this->card->threeDSecure = $secureEcom;


        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Challenge scenario
     *
     * @dataProvider ChallengeSuccessful3DSV2CardTests
     * @param $cardNumber
     * @param $status
     * @throws ApiException
     */
    public function testCardHolderEnrolled_ChallengeRequired_v2($cardNumber, $status)
    {
        $this->card->number = $cardNumber;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

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
        $this->card->threeDSecure = $secureEcom;

        $this->assertEquals($status, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Challenge failed scenario
     *
     * @throws ApiException
     */
    public function testChallengeRequired_GetResultFailed_v2()
    {
        $this->card->number = GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

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
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $secureEcom->status);
    }

    /**
     * Frictionless scenario with tokenize card
     *
     * @throws ApiException
     */
    public function testFullCycle_WithCardTokenization_v2()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_1;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);

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
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $tokenizedCard->threeDSecure = $secureEcom;
        $response = $tokenizedCard->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /**
     * Frictionless scenario different amount between /auth and /initiate
     *
     * @throws ApiException
     */
    public function testFrictionlessFullCycle_v2_DifferentAmount()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);
        $this->assertEquals($this->amount, $secureEcom->getAmount());

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount(9)
            ->withCurrency("USD")
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute();
        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);
        $this->assertEquals($this->amount, $initAuth->getAmount());
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();
        $this->card->threeDSecure = $initAuth;
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);
        $this->assertEquals($this->amount, $secureEcom->getAmount());

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v2_DuplicateAcsRequest()
    {
        $this->card->number = GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

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

        $authClient2 = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient2->setGatewayProvider($this->gatewayProvider);
        $authResponse2 = $authClient2->authenticate_v2($initAuth);
        $this->assertTrue($authResponse2->getStatus());
        $this->assertNotEmpty($authResponse2->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse2->getMerchantData())
            ->execute();
        $this->card->threeDSecure = $secureEcom;

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditSaleTokenized_WithStoredCredentials_Recurring()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_1;
        $this->card->entryMethod = ManualEntryMethod::MOTO;

        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::MERCHANT;
        $storeCredentials->type = StoredCredentialType::RECURRING;
        $storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($tokenizedCard, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $tokenizedCard->threeDSecure = $secureEcom;
        $this->assertEquals("SUCCESS_AUTHENTICATED", $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $tokenizedCard->charge($this->amount)->withCurrency($this->currency)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->cardBrandTransactionId);

        $recurringPayment = $tokenizedCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withStoredCredential($storeCredentials)
            ->withCardBrandStorage(StoredCredentialInitiator::MERCHANT, $response->cardBrandTransactionId)
            ->execute();

        $this->assertNotNull($recurringPayment);
        $this->assertEquals('SUCCESS', $recurringPayment->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $recurringPayment->responseMessage);
        $this->assertNotNull($response->cardBrandTransactionId);
    }

    /**
     * Frictionless scenario with mobile sdk
     */
    public function testFrictionlessFullCycle_v2_WithMobileSdk()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);

        $mobileData = new MobileData();
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
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withOrderTransactionType(OrderTransactionType::GOODS_SERVICE_PURCHASE)
            ->withMobileData($mobileData)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertNotNull($initAuth->acsTransactionId);
        $this->assertNotNull($initAuth->providerServerTransRef);
        $this->assertNotNull($initAuth->acsReferenceNumber);
        $this->assertEquals("05", $initAuth->eci);
        $this->assertEquals("2.2.0", $initAuth->messageVersion);


        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function ChallengeSuccessful3DSV2CardTests()
    {
        return [
            'Challenge v2.1' => [GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_1, Secure3dStatus::SUCCESS_AUTHENTICATED],
            'Challenge v2.2' => [GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_2, Secure3dStatus::SUCCESS_AUTHENTICATED]
        ];
    }

    public function ChallengeRequiredFailed3DSV1CardTests()
    {
        return [
            'Acs Client result code 5' => [5, Secure3dStatus::FAILED],
            'Acs Client result code 7' => [7, Secure3dStatus::SUCCESS_ATTEMPT_MADE],
            'Acs Client result code 9' => [9, Secure3dStatus::NOT_AUTHENTICATED]
        ];
    }

    public function FrictionlessSuccessful3DSV2CardTests()
    {
        return [
            'Frictionless v2.1' => [GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_1, Secure3dStatus::SUCCESS_AUTHENTICATED],
            'Frictionless no method url v2.1' => [GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_NO_METHOD_URL_V2_1, Secure3dStatus::SUCCESS_AUTHENTICATED],
            'Frictionless v2.2' => [GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2, Secure3dStatus::SUCCESS_AUTHENTICATED],
            'Frictionless no method url v2.2' => [GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_NO_METHOD_URL_V2_2, Secure3dStatus::SUCCESS_AUTHENTICATED]
        ];
    }

    public function FrictionlessFailed3DSV2CardTests()
    {
        return [
            'Frictionless failed 1' => [GpApi3DSTestCards::CARD_AUTH_ATTEMPTED_BUT_NOT_SUCCESSFUL_V2_1, Secure3dStatus::SUCCESS_ATTEMPT_MADE],
            'Frictionless failed 2' => [GpApi3DSTestCards::CARD_AUTH_FAILED_V2_1, Secure3dStatus::NOT_AUTHENTICATED],
            'Frictionless failed 3' => [GpApi3DSTestCards::CARD_AUTH_ISSUER_REJECTED_V2_1, Secure3dStatus::FAILED],
            'Frictionless failed 4' => [GpApi3DSTestCards::CARD_AUTH_COULD_NOT_BE_PREFORMED_V2_1, Secure3dStatus::FAILED],
            'Frictionless failed 5' => [GpApi3DSTestCards::CARD_AUTH_ATTEMPTED_BUT_NOT_SUCCESSFUL_V2_2, Secure3dStatus::SUCCESS_ATTEMPT_MADE],
            'Frictionless failed 6' => [GpApi3DSTestCards::CARD_AUTH_FAILED_V2_2, Secure3dStatus::NOT_AUTHENTICATED],
            'Frictionless failed 7' => [GpApi3DSTestCards::CARD_AUTH_ISSUER_REJECTED_V2_2, Secure3dStatus::FAILED],
            'Frictionless failed 8' => [GpApi3DSTestCards::CARD_AUTH_COULD_NOT_BE_PREFORMED_V2_2, Secure3dStatus::FAILED]
        ];
    }


    private function assertCheckEnrollmentChallengeV1(ThreeDSecure $secureEcom)
    {
        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $secureEcom->status);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);
        $this->assertEmpty($secureEcom->eci);
        $this->assertEquals("1.0.0", $secureEcom->messageVersion);
    }

    private function assertCheckEnrollmentCardNotEnrolledV1(ThreeDSecure $secureEcom)
    {
        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::NOT_ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dStatus::NOT_ENROLLED, $secureEcom->status);
        $this->assertEmpty($secureEcom->eci);
        $this->assertEquals('1.0.0', $secureEcom->messageVersion);
        $this->assertEquals('NO', $secureEcom->liabilityShift);
    }

    public function testDecoupledAuth()
    {
        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_1;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->withDecoupledNotificationUrl('https://www.example.com/decoupledNotification')
            ->execute();

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);

        $initAuth = Secure3dService::initiateAuthentication($tokenizedCard, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->withDecoupledFlowRequest(true)
            ->withDecoupledFlowTimeout('9001')
            ->withDecoupledNotificationUrl('https://www.example.com/decoupledNotification')
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $tokenizedCard->threeDSecure = $secureEcom;
        $response = $tokenizedCard->charge($this->amount)->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testExemptionSaleTransaction()
    {
        $this->card->number = GpApi3DSTestCards::CARD_CHALLENGE_REQUIRED_V2_2;

        $threeDS = new ThreeDSecure();
        $threeDS->exemptStatus = ExemptStatus::LOW_VALUE;
        $this->card->threeDSecure = $threeDS;
        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

    }
}