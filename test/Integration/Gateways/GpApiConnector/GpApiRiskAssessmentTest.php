<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AgeIndicator;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\CustomerAuthenticationMethod;
use GlobalPayments\Api\Entities\Enums\DeliveryTimeFrame;
use GlobalPayments\Api\Entities\Enums\OrderTransactionType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\PreOrderIndicator;
use GlobalPayments\Api\Entities\Enums\PriorAuthenticationMethod;
use GlobalPayments\Api\Entities\Enums\ReorderIndicator;
use GlobalPayments\Api\Entities\Enums\RiskAssessmentStatus;
use GlobalPayments\Api\Entities\Enums\ShippingMethod;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\FraudService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GpApiRiskAssessmentTest extends TestCase
{
    /** @var Address */
    private $shippingAddress;

    /** @var BrowserData */
    private $browserData;

    /** @var string */
    private $currency;

    /** @var string|float */
    private $amount;

    /** @var CreditCardData */
    private $card;

    public function setup(): void
    {
        $config = $this->setUpConfig();
        ServicesContainer::configureService($config);

        $this->currency = 'GBP';
        $this->amount = '10.01';

        $this->card = new CreditCardData();
        $this->card->number = '4012001038488884';
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

        //IF WE SET THE screenHeight/screenWidth the API will return a strange error
        $this->browserData = new BrowserData();
        $this->browserData->acceptHeader = "text/html,application/xhtml+xml,application/xml;q=9,image/webp,img/apng,*/*;q=0.8";
        $this->browserData->colorDepth = ColorDepth::TWENTY_FOUR_BITS;
        $this->browserData->ipAddress = "123.123.123.123";
        $this->browserData->javaEnabled = true;
        $this->browserData->javaScriptEnabled = true;
        $this->browserData->language = "en-US";
        $this->browserData->challengWindowSize = ChallengeWindowSize::FULL_SCREEN;
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

    public function testTransactionRiskAnalysisBasicOption()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
        $response = FraudService::riskAssess($this->card)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withIdempotencyKey($idempotencyKey)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
        $this->assertStringStartsWith("RAS_", $response->id);
    }

    public function testTransactionRiskAnalysis_WithIdempotency()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
        $response = FraudService::riskAssess($this->card)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withIdempotencyKey($idempotencyKey)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
        $this->assertStringStartsWith("RAS_", $response->id);

        $errorFound = false;
        try {
            FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withIdempotencyKey($idempotencyKey)
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertStringContainsString("Status Code: DUPLICATE_ACTION - Idempotency Key seen before", $e->getMessage());
            $this->assertEquals('40039', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testRiskAssessmentFullOption()
    {
        $response = FraudService::riskAssess($this->card)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withReferenceNumber('my_EOS_risk_assessment')
            ->withAddressMatchIndicator(false)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withGiftCardAmount(2)
            ->withGiftCardCount(1)
            ->withGiftCardCurrency($this->currency)
            ->withDeliveryEmail('james.mason@example.com')
            ->withDeliveryTimeFrame(DeliveryTimeFrame::SAME_DAY)
            ->withShippingMethod(ShippingMethod::VERIFIED_ADDRESS)
            ->withShippingNameMatchesCardHolderName(false)
            ->withPreOrderIndicator(PreOrderIndicator::FUTURE_AVAILABILITY)
            ->withPreOrderAvailabilityDate(date('Y-m-d H:i:s'))
            ->withReorderIndicator(ReorderIndicator::REORDER)
            ->withOrderTransactionType(OrderTransactionType::GOODS_SERVICE_PURCHASE)
            ->withCustomerAccountId(\GlobalPayments\Api\Utils\GenerationUtils::getGuid())
            ->withAccountAgeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
            ->withAccountCreateDate(date('Y-m-d'))
            ->withAccountChangeDate(date('Y-m-d'))
            ->withAccountChangeIndicator(AgeIndicator::THIS_TRANSACTION)
            ->withPasswordChangeDate(date('Y-m-d'))
            ->withPasswordChangeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
            ->withPhoneNumber('44', '123456789', PhoneNumberType::HOME)
            ->withPhoneNumber('44', '1801555888', PhoneNumberType::WORK)
            ->withPaymentAccountCreateDate(date('Y-m-d'))
            ->withPaymentAccountAgeIndicator(AgeIndicator::LESS_THAN_THIRTY_DAYS)
            ->withPreviousSuspiciousActivity(false)
            ->withNumberOfPurchasesInLastSixMonths(3)
            ->withNumberOfTransactionsInLast24Hours(1)
            ->withNumberOfTransactionsInLastYear(5)
            ->withNumberOfAddCardAttemptsInLast24Hours(1)
            ->withShippingAddressCreateDate(date('Y-m-d H:i:s'))
            ->withShippingAddressUsageIndicator(AgeIndicator::THIS_TRANSACTION)
            ->withPriorAuthenticationMethod(PriorAuthenticationMethod::FRICTIONLESS_AUTHENTICATION)
            ->withPriorAuthenticationTransactionId(GenerationUtils::getGuid())
            ->withPriorAuthenticationTimestamp(date('2022-10-10T16:41:33'))
            ->withPriorAuthenticationData('secret123')
            ->withMaxNumberOfInstallments(5)
            ->withRecurringAuthorizationFrequency(25)
            ->withRecurringAuthorizationExpiryDate(date('Y-m-d'))
            ->withCustomerAuthenticationData('secret123')
            ->withCustomerAuthenticationTimestamp(date('2022-10-10T16:41:33'))
            ->withCustomerAuthenticationMethod(CustomerAuthenticationMethod::MERCHANT_SYSTEM)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
    }

    public function testTransactionRiskAnalysisBasicOption_WithIdempotency()
    {
        /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
        $response = FraudService::riskAssess($this->card)
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withBrowserData($this->browserData)
            ->execute();

        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
    }

    public function testTransactionRiskAnalysis_AllSources()
    {
        $source = array(AuthenticationSource::BROWSER, AuthenticationSource::MERCHANT_INITIATED, AuthenticationSource::MOBILE_SDK);
        foreach ($source as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource($value)
                ->withBrowserData($this->browserData)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_AllDeliveryTimeFrames()
    {
        $deliveryTimeFrame = new DeliveryTimeFrame();
        $reflectionClass = new ReflectionClass($deliveryTimeFrame);
        foreach ($reflectionClass->getConstants() as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->withDeliveryTimeFrame($value)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_AllShippingMethods()
    {
        $shippingMethod = new ShippingMethod();
        $reflectionClass = new ReflectionClass($shippingMethod);
        foreach ($reflectionClass->getConstants() as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->withShippingMethod($value)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_AllOrderTransactionTypes()
    {
        $shippingMethod = new OrderTransactionType();
        $reflectionClass = new ReflectionClass($shippingMethod);
        foreach ($reflectionClass->getConstants() as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->withOrderTransactionType($value)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_AllPriorAuthenticationMethods()
    {
        $shippingMethod = new PriorAuthenticationMethod();
        $reflectionClass = new ReflectionClass($shippingMethod);
        foreach ($reflectionClass->getConstants() as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->withPriorAuthenticationMethod($value)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_AllCustomerAuthenticationMethods()
    {
        $shippingMethod = new CustomerAuthenticationMethod();
        $reflectionClass = new ReflectionClass($shippingMethod);
        foreach ($reflectionClass->getConstants() as $value) {
            /** @var \GlobalPayments\Api\Entities\RiskAssessment $response */
            $response = FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->withCustomerAuthenticationMethod($value)
                ->execute();

            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(RiskAssessmentStatus::ACCEPTED, $response->status);
            $this->assertEquals("Apply Exemption", $response->responseMessage);
            $this->assertStringStartsWith("RAS_", $response->id);
        }
    }

    public function testTransactionRiskAnalysis_MissingAmount()
    {
        $errorFound = false;
        try {
            FraudService::riskAssess($this->card)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following field order.amount", $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testTransactionRiskAnalysis_MissingCurrency()
    {
        $errorFound = false;
        try {
            FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following field order.currency", $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testTransactionRiskAnalysis_MissingSource()
    {
        $errorFound = false;
        try {
            FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following field order.amount", $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testTransactionRiskAnalysis_MissingBrowserData()
    {
        $errorFound = false;
        try {
            FraudService::riskAssess($this->card)
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following field browser_data.accept_header", $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testTransactionRiskAnalysis_MissingCard()
    {
        $errorFound = false;
        try {
            FraudService::riskAssess(new CreditCardData())
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withBrowserData($this->browserData)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following field payment_method.card.number", $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }
}