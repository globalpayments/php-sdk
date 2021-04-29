<?php

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\ChallengeRequestIndicator;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\MerchantInitiatedRequestType;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\ExemptStatus;
use GlobalPayments\Api\Entities\Enums\ExemptionReason;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class Secure3DSExemptionsTest extends TestCase
{
    private $card;
    private $shippingAddress;
    private $billingAddress;
    private $browserData;
    /**
     * @var string
     */
    private $gatewayProvider;

    public function setup()
    {
        $config = $this->getConfig();
        ServicesContainer::configureService($config);
        $this->gatewayProvider = $config->getGatewayProvider();

        // create card data
        $this->card = new CreditCardData();
        $this->card->number = 4263970000005262;
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cardHolderName = 'John Smith';

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Apartment 852';
        $this->shippingAddress->streetAddress2 = 'Complex 741';
        $this->shippingAddress->streetAddress3 = 'no';
        $this->shippingAddress->city = 'Chicago';
        $this->shippingAddress->postalCode = '5001';
        $this->shippingAddress->state = 'IL';
        $this->shippingAddress->countryCode = '840';

        // billing address
        $this->billingAddress = new Address();
        $this->billingAddress->streetAddress1 = 'Flat 456';
        $this->billingAddress->streetAddress2 = 'House 789';
        $this->billingAddress->streetAddress3 = 'no';
        $this->billingAddress->city = 'Halifax';
        $this->billingAddress->postalCode = 'W5 9HR';
        $this->billingAddress->countryCode = '826';

        // browser data
        $this->browserData = new BrowserData();
        $this->browserData->acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=9,image/webp,img/apng,*/*;q=0.8';
        $this->browserData->colorDepth = ColorDepth::TWENTY_FOUR_BITS;
        $this->browserData->ipAddress = '123.123.123.123';
        $this->browserData->javaEnabled = true;
        $this->browserData->javaScriptEnabled = true;
        $this->browserData->language = 'en';
        $this->browserData->screenHeight = 1080;
        $this->browserData->screenWidth = 1920;
        $this->browserData->challengWindowSize = ChallengeWindowSize::WINDOWED_600X400;
        $this->browserData->timeZone = '0';
        $this->browserData->userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64, x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36';
    }

    protected function getConfig()
    {
        $config = new GpEcomConfig();
        $config->merchantId = 'myMerchantId';
        $config->accountId = 'ecomeos';
        $config->sharedSecret = 'secret';
        $config->methodNotificationUrl = 'https://www.example.com/methodNotificationUrl';
        $config->challengeNotificationUrl = 'https://www.example.com/challengeNotificationUrl';
        $config->secure3dVersion = Secure3dVersion::ANY;
        $config->merchantContactUrl = 'https://www.example.com';

        return $config;
    }

    /**
     * 'APPLY_EXEMPTION' - Amount is less than or equal to 250 EUR (or converted equivalent)
     * The 3D Secure Service will populate the outbound authentication message with the appropriate exemption flag.
     */
    public function testFullCycle_v2_ApplyExemption()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertTrue($secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

        // initiate authentication
        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount(10.01)
            ->withCurrency('USD')
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withChallengeRequestIndicator(ChallengeRequestIndicator::NO_PREFERENCE)
            ->withMerchantInitiatedRequestType(MerchantInitiatedRequestType::TOP_UP)
            ->withWhitelistStatus(true)
            ->withDecoupledFlowRequest(false)
            ->withDecoupledFlowTimeout('9001')
            ->withDecoupledNotificationUrl('https://example-value.com')
            ->withEnableExemptionOptimization(true)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(ExemptionReason::APPLY_EXEMPTION, $initAuth->exemptReason);
        $this->assertEquals(ExemptStatus::TRANSACTION_RISK_ANALYSIS, $initAuth->exemptStatus);
        // get authentication data
        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $this->assertEquals(ExemptStatus::TRANSACTION_RISK_ANALYSIS, $secureEcom->exemptStatus);
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);

        $response = $this->card->charge(10.01)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * 'CONTINUE' - Amount is above 250 EUR and less than or equal to 500 EUR (or converted equivalent)
     * The 3D Secure Service will populate the outbound authentication as normal.
     */
    public function testFullCycle_v2_EOS_Continue()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertTrue($secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        // initiate authentication
        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount(300)
            ->withCurrency('EUR')
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withChallengeRequestIndicator(ChallengeRequestIndicator::NO_PREFERENCE)
            ->withMerchantInitiatedRequestType(MerchantInitiatedRequestType::TOP_UP)
            ->withWhitelistStatus(true)
            ->withDecoupledFlowRequest(false)
            ->withDecoupledFlowTimeout('9001')
            ->withDecoupledNotificationUrl('https://example-value.com')
            ->withEnableExemptionOptimization(true)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(ExemptionReason::EOS_CONTINUE, $initAuth->exemptReason);
        $this->assertNull($initAuth->exemptStatus);
        // get authentication data
        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $this->assertNull($secureEcom->exemptStatus);
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);

        $response = $this->card->charge(300)
            ->withCurrency('EUR')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * 'FORCE_SECURE' - Amount is above 500 EUR and less than or equal to 750 EUR (or converted equivalent)
     * The 3D Secure Service will populate the outbound authentication message indicating a challenge is mandated.
     * This will always force a challenge to be applied, regardless of test card used.
     */
    public function testFullCycle_v2_EOS_ForceSecure()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertTrue($secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        // initiate authentication
        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount(550)
            ->withCurrency('EUR')
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withChallengeRequestIndicator(ChallengeRequestIndicator::NO_PREFERENCE)
            ->withMerchantInitiatedRequestType(MerchantInitiatedRequestType::TOP_UP)
            ->withWhitelistStatus(true)
            ->withDecoupledFlowRequest(false)
            ->withDecoupledFlowTimeout('9001')
            ->withDecoupledNotificationUrl('https://example-value.com')
            ->withEnableExemptionOptimization(true)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertNull($initAuth->exemptStatus);
        $this->assertEquals('CHALLENGE_REQUIRED', $initAuth->status);
        $this->assertEquals(ExemptionReason::FORCE_SECURE, $initAuth->exemptReason);

        // get authentication data
        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $this->assertNull($secureEcom->exemptStatus);
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals('CHALLENGE_REQUIRED', $secureEcom->status);
    }

    /**
     * 'BLOCK' - Amount is above 750 EUR (or converted equivalent)
     * The transaction will be blocked, and a 202 Accepted response will be returned.
     */
    public function testFullCycle_v2_EOS_Block()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);
        $this->assertTrue($secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        // initiate authentication
        try {
            Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(800)
                ->withCurrency('EUR')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                ->withChallengeRequestIndicator(ChallengeRequestIndicator::NO_PREFERENCE)
                ->withMerchantInitiatedRequestType(MerchantInitiatedRequestType::TOP_UP)
                ->withWhitelistStatus(true)
                ->withDecoupledFlowRequest(false)
                ->withDecoupledFlowTimeout('9001')
                ->withDecoupledNotificationUrl('https://example-value.com')
                ->withEnableExemptionOptimization(true)
                ->execute();
        } catch (GatewayException $exception) {
            $this->assertEquals('Status Code: 202 - Blocked by Transaction Risk Analysis.' , $exception->getMessage());
        }
    }
}