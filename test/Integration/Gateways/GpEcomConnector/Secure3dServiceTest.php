<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use DateTime;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\ {
    AddressType, AgeIndicator, AuthenticationRequestType, AuthenticationSource,
    ChallengeRequestIndicator, ChallengeWindowSize, ColorDepth, CustomerAuthenticationMethod, DeliveryTimeFrame,
    MerchantInitiatedRequestType, MessageCategory, MethodUrlCompletion, OrderTransactionType, PreOrderIndicator,
    PriorAuthenticationMethod, ReorderIndicator, SdkInterface, SdkUiType, Secure3dStatus, Secure3dVersion,
    ShippingMethod, GatewayProvider
};
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Tests\Integration\Gateways\ThreeDSecureAcsClient;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class Secure3dServiceTest extends TestCase
{
    private CreditCardData $card;
    private RecurringPaymentMethod $stored;
    private Address $shippingAddress;
    private Address $billingAddress;
    private BrowserData $browserData;
    private GatewayProvider|string $gatewayProvider;

    public function setup(): void
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

        // stored card
        $this->stored = new RecurringPaymentMethod('20190809-Realex', '20190809-Realex-Credit');

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

    protected function getConfig(): GpEcomConfig
    {
        $config = new GpEcomConfig();
        $config->merchantId = 'myMerchantId';
        $config->accountId = 'ecom3ds';
        $config->sharedSecret = 'secret';
        $config->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net';
        $config->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net';
        $config->secure3dVersion = Secure3dVersion::ANY;
        $config->merchantContactUrl = 'https://www.example.com';
//        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
//        $config->webProxy = new CustomWebProxy('127.0.0.1:8866');

        return $config;
    }

    public function testFullCycle_v2()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);

        if ($secureEcom->enrolled) {
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
                ->withMobileNumber('+44', '11 33 44 44')
                ->withHomeNumber('44', '12444555')
                ->withWorkNumber('+44', '345 6667')
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testFullCycle_v2_FrictionlessCards()
    {
        $cards = array("4263970000005262" => "2.1.0", "4222000006724235" => "2.1.0", "4222000006285344" => "2.2.0", "4222000009719489" => "2.2.0");

        foreach ($cards as $cardNumber => $cardVersion) {
            $this->card->number = $cardNumber;
            $secureEcom = Secure3dService::checkEnrollment($this->card)
                ->execute('default', Secure3dVersion::TWO);
            $this->assertNotNull($secureEcom);
            $this->assertNotNull($secureEcom->serverTransactionId);

            if ($secureEcom->enrolled) {
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
                    ->execute();

                $this->assertNotNull($initAuth);
                $this->assertEquals(Secure3dVersion::TWO, $initAuth->getVersion());
                $this->assertEquals('AUTHENTICATION_SUCCESSFUL' , $initAuth->status);
                $this->assertNotNull($initAuth->issuerAcsUrl);
                $this->assertNotNull($initAuth->acsTransactionId);
                $this->assertNotNull($initAuth->acsReferenceNumber);
                $this->assertNotNull($initAuth->authenticationValue);
                $this->assertNotNull($initAuth->serverTransactionId);
                $this->assertNull($initAuth->challengeMandated);
                $this->assertEquals('05', $initAuth->eci);
                $this->assertEquals($cardVersion, $initAuth->acsEndVersion);

                // get authentication data
                $secureEcom = Secure3dService::getAuthenticationData()
                    ->withServerTransactionId($initAuth->serverTransactionId)
                    ->execute();
                $this->card->threeDSecure = $secureEcom;

                if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                    $response = $this->card->charge(10.01)
                        ->withCurrency('USD')
                        ->execute();

                    $this->assertNotNull($response);
                    $this->assertEquals('00', $response->responseCode);
                } else {
                    $this->fail('Signature verification failed.');
                }
            } else {
                $this->fail('Card not enrolled');
            }
        }
    }

    public function testFullCycle_v2_1_challenge()
    {
        $this->card->number = '4012001038488884';
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
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withBrowserData($this->browserData)
            ->withMobileNumber('+44', '11 33 44 44')
            ->withHomeNumber('44', '12444555')
            ->withWorkNumber('+44', '345 6667')
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dVersion::TWO, $initAuth->getVersion());
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertEmpty($initAuth->eci);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->gatewayProvider);
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        // get authentication data
        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();
        $this->card->threeDSecure = $secureEcom;
        $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);

        $response = $this->card->charge(10.01)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testFullCycle_v2_ChallengeRequiredCards()
    {
        $cards = array("4012001038488884" => "2.1.0", "4222000001227408" => "2.2.0");

        foreach ($cards as $cardNumber => $cardVersion) {
            $this->card->number = $cardNumber;
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
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                ->withBrowserData($this->browserData)
                ->execute();

            $this->assertNotNull($initAuth);
            $this->assertEquals(Secure3dVersion::TWO, $initAuth->getVersion());
            $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
            $this->assertNotNull($initAuth->issuerAcsUrl);
            $this->assertNotNull($initAuth->payerAuthenticationRequest);
            $this->assertNotNull($initAuth->acsTransactionId);
            $this->assertNotNull($initAuth->acsReferenceNumber);
            $this->assertNotNull($initAuth->authenticationType);
            $this->assertNotNull($initAuth->challengeMandated);
            $this->assertEmpty($initAuth->eci);
            $this->assertEquals($cardVersion, $initAuth->acsEndVersion);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;
            $this->assertEquals('CHALLENGE_REQUIRED', $secureEcom->status);

            $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
            $authClient->setGatewayProvider($this->gatewayProvider);
            $authResponse = $authClient->authenticate_v2($initAuth);
            $this->assertTrue($authResponse->getStatus());
            $this->assertNotEmpty($authResponse->getMerchantData());

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;
            $this->assertEquals('AUTHENTICATION_SUCCESSFUL', $secureEcom->status);

            $response = $this->card->charge(10.01)
                ->withCurrency('USD')
                ->execute();

            $this->assertNotNull($response);
            $this->assertEquals('00', $response->responseCode);
        }
    }

    public function testFullCycle_v2_2()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);

        if ($secureEcom->enrolled) {
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
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testFullCycle_Any()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withAmount(1.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            if ($secureEcom->getVersion() == Secure3dVersion::TWO) {
                // initiate authentication
                $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                    ->withAmount(10.01)
                    ->withCurrency('USD')
                    ->withOrderCreateDate(date('Y-m-d H:i:s'))
                    ->withAddress($this->billingAddress, AddressType::BILLING)
                    ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                    ->withBrowserData($this->browserData)
                    ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                    ->execute();
                $this->assertNotNull($initAuth);

                // get authentication data
                $secureEcom = Secure3dService::getAuthenticationData()
                    ->withServerTransactionId($initAuth->serverTransactionId)
                    ->execute();
                $this->card->threeDSecure = $secureEcom;

                if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                    $response = $this->card->charge(10.01)
                        ->withCurrency('USD')
                        ->execute();

                    $this->assertNotNull($response);
                    $this->assertEquals('00', $response->responseCode);
                } else {
                    $this->fail('Signature verification failed.');
                }
            } else {
                // authenticate
                $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
                $authClient->setGatewayProvider($this->gatewayProvider);
                $authResponse = $authClient->authenticate(
                    $secureEcom->payerAuthenticationRequest,
                    $secureEcom->getMerchantData()->toString()
                );

                $payerAuthenticationResponse = $authResponse->getAuthResponse();
                $md = MerchantDataCollection::parse($authResponse->getMerchantData());

                $secureEcom = Secure3dService::getAuthenticationData()
                    ->withPayerAuthenticationResponse($payerAuthenticationResponse)
                    ->withMerchantData($md)
                    ->execute();
                $this->card->threeDSecure = $secureEcom;

                if ($secureEcom->status == 'Y') {
                    $response = $this->card->charge()->execute();
                    $this->assertNotNull($response);
                    $this->assertEquals('00', $response->responseCode);
                } else {
                    $this->fail('Signature verification failed.');
                }
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testFullCycle_v2_StoredCard()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->stored)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->stored, $secureEcom)
                ->withAmount(10.01)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->stored->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->stored->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testFullCycle_v2_OTB()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);

        if ($secureEcom->enrolled) {
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
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->verify()
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testFullCycle_v2_OTB_StoredCard()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->stored)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertNotNull($secureEcom->serverTransactionId);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->stored, $secureEcom)
                ->withAmount(10.01)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->stored->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->stored->verify()
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalRequestLevelFields()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
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

                // optionals
                ->withMerchantInitiatedRequestType(AuthenticationRequestType::RECURRING_TRANSACTION)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalOrderLevelFields()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(250.00)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)

                // optionals
                ->withGiftCardCurrency('USD')
                ->withGiftCardAmount(250.00)
                ->withDeliveryEmail('james.mason@example.com')
                ->withDeliveryTimeFrame(DeliveryTimeFrame::ELECTRONIC_DELIVERY)
                ->withShippingMethod(ShippingMethod::VERIFIED_ADDRESS)
                ->withShippingNameMatchesCardHolderName(true)
                ->withPreOrderIndicator(PreOrderIndicator::FUTURE_AVAILABILITY)
                // TODO
                // This value passed for date, but doesn't seem right
                // This line bugged in Java SDK
                ->withPreOrderAvailabilityDate('20190418')
                ->withReorderIndicator(ReorderIndicator::REORDER)
                ->withOrderTransactionType(OrderTransactionType::GOODS_SERVICE_PURCHASE)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalPayerLevelFields()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(250.00)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)

                // optionals
                ->withCustomerAccountId('6dcb24f5-74a0-4da3-98da-4f0aa0e88db3')
                ->withAccountAgeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
                ->withAccountCreateDate('20190110')
                ->withAccountChangeDate('20190128')
                ->withAccountChangeIndicator(AgeIndicator::THIS_TRANSACTION)
                ->withPasswordChangeDate('20190115')
                ->withPasswordChangeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
                ->withHomeNumber('44', '123456798')
                ->withWorkNumber('44', '1801555888')
                ->withPaymentAccountCreateDate('20190101')
                ->withPaymentAccountAgeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
                ->withPreviousSuspiciousActivity(false)
                ->withNumberOfPurchasesInLastSixMonths(3)
                ->withNumberOfTransactionsInLast24Hours(1)
                ->withNumberOfTransactionsInLastYear(5)
                ->withNumberOfAddCardAttemptsInLast24Hours(1)
                ->withShippingAddressCreateDate('20190128')
                ->withShippingAddressUsageIndicator(AgeIndicator::THIS_TRANSACTION)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalPriorAuthenticationData()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(250.00)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)

                // optionals
                ->withPriorAuthenticationMethod(PriorAuthenticationMethod::FRICTIONLESS_AUTHENTICATION)
                ->withPriorAuthenticationTransactionId('26c3f619-39a4-4040-bf1f-6fd433e6d615')
                ->withPriorAuthenticationTimestamp((new DateTime('2019-01-10T12:57:33.333Z'))->format(DateTime::RFC3339_EXTENDED))
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalRecurringData()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(250.00)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)

                // optionals
                ->withMaxNumberOfInstallments(5)
                ->withRecurringAuthorizationFrequency(25)
                ->withRecurringAuthorizationExpiryDate('20190825')
                ->withAuthenticationRequestType(AuthenticationRequestType::INSTALMENT_TRANSACTION)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalPayerLoginData()
    {
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);

        if ($secureEcom->enrolled) {
            $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());

            // initiate authentication
            $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
                ->withAmount(250.00)
                ->withCurrency('USD')
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withBrowserData($this->browserData)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)

                // optionals
                ->withCustomerAuthenticationData('string')
                ->withCustomerAuthenticationTimestamp((new DateTime('2019-01-10T12:57:33.333Z'))->format(DateTime::RFC3339_EXTENDED))
                ->withCustomerAuthenticationMethod(CustomerAuthenticationMethod::MERCHANT_SYSTEM)
                ->execute();
            $this->assertNotNull($initAuth);

            // get authentication data
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($initAuth->serverTransactionId)
                ->execute();
            $this->card->threeDSecure = $secureEcom;

            if ($secureEcom->status == 'AUTHENTICATION_SUCCESSFUL') {
                $response = $this->card->charge(10.01)
                    ->withCurrency('USD')
                    ->execute();

                $this->assertNotNull($response);
                $this->assertEquals('00', $response->responseCode);
            } else {
                $this->fail('Signature verification failed.');
            }
        } else {
            $this->fail('Card not enrolled');
        }
    }

    public function testOptionalMobileFields()
    {
        $this->card->number = '4012001038488884';
        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->execute('default', Secure3dVersion::TWO);
        $this->assertNotNull($secureEcom);
        $this->assertTrue($secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $phemeralPublicKey = [
            "kty" => "EC",
            "crv" => "P-256",
            "x" => "WWcpTjbOqiu_1aODllw5rYTq5oLXE_T0huCPjMIRbkI",
            "y" => "Wz_7anIeadV8SJZUfr4drwjzuWoUbOsHp5GdRZBAAiw"
        ];
        // initiate authentication
        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount(250.00)
            ->withCurrency('USD')
            ->withAuthenticationSource(AuthenticationSource::MOBILE_SDK)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withOrderId($secureEcom->getOrderId())
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddressMatchIndicator(false)
            ->withMethodUrlCompletion(MethodUrlCompletion::NO)
            ->withMessageCategory(MessageCategory::PAYMENT_AUTHENTICATION)
            ->withCustomerEmail('custumer@domain.com')
            // optionals
            ->withApplicationId('f283b3ec-27da-42a1-acea-f3f70e75bbdc')
            ->withSdkInterface(SdkInterface::BOTH)
            ->withSdkUiTypes([SdkUiType::TEXT, SdkUiType::SINGLE_SELECT, SdkUiType::MULTI_SELECT, SdkUiType::OOB, SdkUiType::HTML_OTHER])
            ->withReferenceNumber('3DS_LOA_SDK_PPFU_020100_00007')
            ->withSdkTransactionId('b2385523-a66c-4907-ac3c-91848e8c0067')
            ->withEncodedData('ew0KCSJEViI6ICIxLjAiLA0KCSJERCI6IHsNCgkJIkMwMDEiOiAiQW5kcm9pZCIsDQoJCSJDMDAyIjogIkhUQyBPbmVfTTgiLA0KCQkiQzAwNCI6ICI1LjAuMSIsDQoJCSJDMDA1IjogImVuX1VTIiwNCgkJIkMwMDYiOiAiRWFzdGVybiBTdGFuZGFyZCBUaW1lIiwNCgkJIkMwMDciOiAiMDY3OTc5MDMtZmI2MS00MWVkLTk0YzItNGQyYjc0ZTI3ZDE4IiwNCgkJIkMwMDkiOiAiSm9obidzIEFuZHJvaWQgRGV2aWNlIg0KCX0sDQoJIkRQTkEiOiB7DQoJCSJDMDEwIjogIlJFMDEiLA0KCQkiQzAxMSI6ICJSRTAzIg0KCX0sDQoJIlNXIjogWyJTVzAxIiwgIlNXMDQiXQ0KfQ0K')
            ->withMaximumTimeout(5)
            ->withEphemeralPublicKey($phemeralPublicKey)
            ->execute();

        $this->assertNotNull($initAuth);
        $this->assertEquals('CHALLENGE_REQUIRED', $initAuth->status);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertNotNull($initAuth->acsTransactionId);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);
        $this->assertNotNull($initAuth->acsUiTemplate);
        $this->assertNotNull($initAuth->acsInterface);
        $this->assertNotNull($initAuth->acsReferenceNumber);

        // get authentication data
        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($initAuth->serverTransactionId)
            ->execute();

        $this->assertEquals('CHALLENGE_REQUIRED', $secureEcom->status);
        $this->card->threeDSecure = $secureEcom;

        $response = $this->card->charge(10.01)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

}
