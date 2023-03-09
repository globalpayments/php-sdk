<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\ChallengeRequestIndicator;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialReason;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;
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

class GpApi3DS1Test extends TestCase
{
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
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_ENROLLED_V1;
        $this->card->expMonth = '12';
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
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
     * Tests for 3DS v1 Card Enrolled - Check Availability
     */
    public function testCardHolderEnrolled_ChallengeRequired_v1()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v1_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

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

    public function testCardHolderEnrolled_ChallengeRequired_v1_TokenizedCard()
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

        $this->assertCheckEnrollmentChallengeV1($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v1_AllPreferenceValues()
    {
        $challengeRequestIndicator = new ChallengeRequestIndicator();
        $reflectionClass = new ReflectionClass($challengeRequestIndicator);
        foreach ($reflectionClass->getConstants() as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withChallengeRequestIndicator($value)
                ->execute();

            $this->assertCheckEnrollmentChallengeV1($secureEcom);
        }
    }

    public function testCardHolderEnrolled_ChallengeRequired_v1_StoredCredentials()
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

        $this->assertCheckEnrollmentChallengeV1($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v1_AllSources()
    {
        $source = array("BROWSER", "MERCHANT_INITIATED", "MOBILE_SDK", "STORED_RECURRING");
        foreach ($source as $value) {
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->withCurrency($this->currency)
                ->withAmount($this->amount)
                ->withAuthenticationSource($value)
                ->execute();
        }

        $this->assertCheckEnrollmentChallengeV1($secureEcom);
    }

    public function testCardHolderEnrolled_ChallengeRequired_v1_WithNullPaymentMethod()
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
     * Tests for 3DS v1 Card Not Enrolled - Check Availability
     */
    public function testCardHolderNotEnrolled_v1()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_NOT_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentCardNotEnrolledV1($secureEcom);
    }

    public function testCardHolderNotEnrolled_v1_TokenizedCard()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_NOT_ENROLLED_V1;

        $response = $this->card->tokenize()->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $secureEcom = Secure3dService::checkEnrollment($tokenizedCard)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentCardNotEnrolledV1($secureEcom);
    }

    /**
     * Tests for 3DS v1 Card Enrolled - Obtain Result
     */
    public function testCardHolderEnrolled_PostResult()
    {
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

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('5', $secureEcom->eci);
        $this->assertEquals('1.0.0', $secureEcom->messageVersion);
    }

    public function testCardHolderEnrolled_PostResult_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

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
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('5', $secureEcom->eci);
        $this->assertEquals('1.0.0', $secureEcom->messageVersion);

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($secureEcom->serverTransactionId)
                ->withPayerAuthenticationResponse($authResponse->getAuthResponse())
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

    public function testCardHolderEnrolled_PostResult_NonExistentId()
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

    public function testCardHolderEnrolled_PostResult_AcsNotComplete()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentChallengeV1($secureEcom);

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($secureEcom->serverTransactionId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50027', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Undefined element in Message before PARes', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * Tests for 3DS v1 Card Not Enrolled - Obtain Result
     */
    public function testCardHolderNotEnrolled_PostResult()
    {
        $this->card->number = GpApi3DSTestCards::CARDHOLDER_NOT_ENROLLED_V1;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute();

        $this->assertCheckEnrollmentCardNotEnrolledV1($secureEcom);

        $exceptionCaught = false;
        try {
            Secure3dService::getAuthenticationData()
                ->withServerTransactionId($secureEcom->serverTransactionId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50027', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Undefined element in Message before PARes', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
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
        $this->assertEquals("NO", $secureEcom->liabilityShift);
    }

    private function assertCheckEnrollmentCardNotEnrolledV1(ThreeDSecure $secureEcom)
    {
        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dVersion::ONE, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::NOT_ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dStatus::NOT_ENROLLED, $secureEcom->status);
        $this->assertEmpty($secureEcom->eci);
        $this->assertEquals('1.0.0', $secureEcom->messageVersion);
        $this->assertEquals("NO", $secureEcom->liabilityShift);
    }

}