<?php

namespace Gateways\GpApiConnector;

use DateTime;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\PayByLinkSortProperty;
use GlobalPayments\Api\Entities\Enums\PayByLinkStatus;
use GlobalPayments\Api\Entities\Enums\PayByLinkType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\PayByLinkData;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\PayByLinkSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\PayByLinkService;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Data\GpApi3DSTestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class GpApiPayByLinkTest extends TestCase
{
    private DateTime $startDate;
    private DateTime $endDate;
    private float $amount = 2.11;
    private PayByLinkData $payByLink;
    private CreditCardData $card;
    private Address $shippingAddress;
    private BrowserData $browserData;
    private string $payByLinkId;

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $this->payByLink = new PayByLinkData();
        $this->payByLink->type = PayByLinkType::PAYMENT;
        $this->payByLink->usageMode = PaymentMethodUsageMode::SINGLE;
        $this->payByLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $this->payByLink->usageLimit = 3;
        $this->payByLink->name = 'Mobile Bill Payment';
        $this->payByLink->isShippable = true;
        $this->payByLink->shippingAmount = 1.23;
        $this->payByLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $this->payByLink->images = [];
        $this->payByLink->returnUrl = 'https://www.example.com/returnUrl';
        $this->payByLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $this->payByLink->cancelUrl = 'https://www.example.com/returnUrl';

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
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

        $response = PayByLinkService::findPayByLink(1, 1)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYBYLINK_STATUS, PayByLinkStatus::ACTIVE)
            ->execute();
        if (count($response->result) == 1) {
            $this->payByLinkId = $response->result[0]->id;
        }
    }

    public function setUpConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = 'v2yRaFOLwFaQc0fSZTCyAdQCBNByGpVK';
        $config->appKey = 'oKZpWitk6tORoCVT';
        $config->channel = Channel::CardNotPresent;
        $config->environment = Environment::TEST;
        $config->country = 'GB';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'LinkManagement';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function testReportPayByLinkDetail()
    {
        $payBylinkId = 'LNK_GderFbTKFzj7X507E7OgDfuRnlKViP';
        /** @var PayByLinkSummary $response */
        $response = PayByLinkService::payByLinkDetail($payBylinkId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(PayByLinkSummary::class, $response);
        $this->assertEquals($payBylinkId, $response->id);
    }

    public function testReportPayByLinkDetail_RandomId()
    {
        $payByLinkId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            PayByLinkService::payByLinkDetail($payByLinkId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals("Status Code: RESOURCE_NOT_FOUND - Links " . $payByLinkId . " not found at this /ucp/links/" . $payByLinkId . "", $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindPayByLinkByDate()
    {
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
    }

    public function testCreatePayByLink()
    {
        $payByLink = new PayByLinkData();
        $payByLink->type = PayByLinkType::PAYMENT;
        $payByLink->usageMode = PaymentMethodUsageMode::SINGLE;
        $payByLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $payByLink->usageLimit = 1;
        $payByLink->name = 'Mobile Bill Payment';
        $payByLink->isShippable = true;
        $payByLink->shippingAmount = 1.23;
        $payByLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $payByLink->images = [];
        $payByLink->returnUrl = 'https://www.example.com/returnUrl';
        $payByLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $payByLink->cancelUrl = 'https://www.example.com/returnUrl';

        $response = PayByLinkService::create($payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);
        $this->assertEquals("YES", $response->payByLinkResponse->isShippable);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));
    }

    public function testCreatePayByLink_MultipleUsage()
    {
        $payByLink = new PayByLinkData();
        $payByLink->type = PayByLinkType::PAYMENT;
        $payByLink->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $payByLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $payByLink->usageLimit = 2;
        $payByLink->name = 'Mobile Bill Payment';
        $payByLink->isShippable = true;
        $payByLink->shippingAmount = 1.23;
        $payByLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $payByLink->images = [];
        $payByLink->returnUrl = 'https://www.example.com/returnUrl';
        $payByLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $payByLink->cancelUrl = 'https://www.example.com/returnUrl';

        $response = PayByLinkService::create($payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));
    }

    public function testCreatePayByLink_ThenCharge()
    {
        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);
        $this->assertEquals("YES", $response->payByLinkResponse->isShippable);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $transaction = $this->card->charge($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payByLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayByLinkService::payByLinkDetail($response->payByLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayByLinkSummary::class, $getResponse);
        $this->assertEquals($response->payByLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayByLink_MultipleUsage_ThenCharge()
    {
        $this->payByLink->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $this->payByLink->usageLimit = 2;

        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        for ($i = 1; $i <= $this->payByLink->usageLimit; $i++) {
            $transaction = $this->card->charge($this->amount)
                ->withCurrency('GBP')
                ->withPaymentLinkId($response->payByLinkResponse->id)
                ->execute("createTransaction");

            $this->assertNotNull($transaction);
            $this->assertEquals('SUCCESS', $transaction->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
        }

        sleep(2);

        $getResponse = PayByLinkService::payByLinkDetail($response->payByLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayByLinkSummary::class, $getResponse);
        $this->assertEquals($response->payByLinkResponse->id, $getResponse->id);
        $this->assertCount($this->payByLink->usageLimit, $getResponse->transactions);
        $this->assertEquals(0, intval($getResponse->viewedCount));
        $this->assertEquals(0, intval($getResponse->usageCount));
    }

    public function testCreatePayByLink_ThenAuthorizeAndCapture()
    {
        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $authorize = $this->card->authorize($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payByLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($authorize);
        $this->assertEquals('SUCCESS', $authorize->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authorize->responseMessage);

        $capture = $authorize->capture($this->amount)
            ->execute("createTransaction");

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);

        sleep(2);

        $getResponse = PayByLinkService::payByLinkDetail($response->payByLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayByLinkSummary::class, $getResponse);
        $this->assertEquals($response->payByLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayByLink_ThenCharge_WithTokenizedCard()
    {
        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));

        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->permissions = ['PMT_POST_Create_Single'];
        ServicesContainer::configureService($config, "singleUseToken");

        $tokenResponse = $this->card->tokenize(true, PaymentMethodUsageMode::SINGLE)
            ->execute("singleUseToken");
        $tokenId = $tokenResponse->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");
        $transaction = $tokenizedCard->charge($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payByLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayByLinkService::payByLinkDetail($response->payByLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayByLinkSummary::class, $getResponse);
        $this->assertEquals($response->payByLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayByLink_ThenCharge_With3DS()
    {
        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);

        fwrite(STDERR, print_r($response->payByLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $this->card->number = GpApi3DSTestCards::CARD_AUTH_SUCCESSFUL_V2_2;

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency('GBP')
            ->withAmount($this->amount)
            ->execute("createTransaction");

        $this->assertNotNull($secureEcom);
        $this->assertEquals(Secure3dStatus::ENROLLED, $secureEcom->enrolled);
        $this->assertEquals(Secure3dVersion::TWO, $secureEcom->getVersion());
        $this->assertEquals(Secure3dStatus::AVAILABLE, $secureEcom->status);
        $this->assertNotNull($secureEcom->issuerAcsUrl);
        $this->assertNotNull($secureEcom->payerAuthenticationRequest);

        $initAuth = Secure3dService::initiateAuthentication($this->card, $secureEcom)
            ->withAmount($this->amount)
            ->withCurrency('GBP')
            ->withAuthenticationSource(AuthenticationSource::BROWSER)
            ->withMethodUrlCompletion(MethodUrlCompletion::YES)
            ->withOrderCreateDate(date('Y-m-d H:i:s'))
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withBrowserData($this->browserData)
            ->execute("createTransaction");

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $initAuth->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($secureEcom->serverTransactionId)
            ->execute("createTransaction");

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);

        $this->card->threeDSecure = $secureEcom;

        $transaction = $this->card->charge($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payByLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayByLinkService::payByLinkDetail($response->payByLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayByLinkSummary::class, $getResponse);
        $this->assertEquals($response->payByLinkResponse->id, $getResponse->id);
    }

    public function testEditPayByLink()
    {
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYBYLINK_STATUS, PayByLinkStatus::ACTIVE)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
        $this->assertNotNull($randomPayByLink->id);

        $payByLinkData = new PayByLinkData();
        $payByLinkData->name = 'bla bla bla';
        $payByLinkData->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $payByLinkData->type = PayByLinkType::PAYMENT;
        $payByLinkData->usageLimit = 5;
        $payByLinkData->isShippable = false;
        $amount = 10.08;
        $response = PayByLinkService::edit($randomPayByLink->id)
            ->withAmount($amount)
            ->withPayByLinkData($payByLinkData)
            ->withDescription('Update PayByLink description')
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(PayByLinkStatus::ACTIVE, $response->responseMessage);
        $this->assertEquals($amount, $response->balanceAmount);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
    }

    public function testCreatePayByLink_MissingType()
    {
        $this->payByLink->type = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->withDescription('March and April Invoice')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field type', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingUsageMode()
    {
        $this->payByLink->usageMode = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->withDescription('March and April Invoice')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field usage_mode', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingPaymentMethod()
    {
        $this->payByLink->allowedPaymentMethods = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->withDescription('March and April Invoice')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field transactions.allowed_payment_methods', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingName()
    {
        $this->payByLink->name = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->withDescription('March and April Invoice')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field name', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingShippable()
    {
        $this->payByLink->isShippable = null;

        $response = PayByLinkService::create($this->payByLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayByLinkResponse($response);
        $this->assertEquals("NO", $response->payByLinkResponse->isShippable);
    }

    public function testCreatePayByLink_MissingShippingAmount()
    {
        $this->payByLink->shippingAmount = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->withDescription('March and April Invoice')
                ->withClientTransactionId(GenerationUtils::getGuid())
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: shipping_amount.', $e->getMessage());
            $this->assertEquals('40251', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingDescription()
    {
        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withCurrency('GBP')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field description', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayByLink_MissingCurrency()
    {
        $exceptionCaught = false;
        try {
            PayByLinkService::create($this->payByLink, $this->amount)
                ->withDescription('March and April Invoice')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following field transactions.currency', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_MissingUsageMode()
    {
        $this->assertNotNull($this->payByLinkId);
        $this->payByLink->usageMode = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::edit($this->payByLinkId)
                ->withAmount($this->amount)
                ->withPayByLinkData($this->payByLink)
                ->withDescription('Update PayByLink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('usageMode cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_MissingType()
    {
        $this->assertNotNull($this->payByLinkId);
        $this->payByLink->type = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::edit($this->payByLinkId)
                ->withAmount($this->amount)
                ->withPayByLinkData($this->payByLink)
                ->withDescription('Update PayByLink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('type cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_MissingUsageLimit()
    {
        $this->assertNotNull($this->payByLinkId);
        $this->payByLink->usageLimit = null;

        $exceptionCaught = false;
        try {
            PayByLinkService::edit($this->payByLinkId)
                ->withAmount($this->amount)
                ->withPayByLinkData($this->payByLink)
                ->withDescription('Update PayBylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('usageLimit cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_MissingPayByLinkData()
    {
        $this->assertNotNull($this->payByLinkId);
        $exceptionCaught = false;
        try {
            PayByLinkService::edit($this->payByLinkId)
                ->withAmount($this->amount)
                ->withDescription('Update PayByLink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Property `usageMode` does not exist on `GlobalPayments\Api\Builders\ManagementBuilder`', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_MissingAmount()
    {
        $this->assertNotNull($this->payByLinkId);
        $exceptionCaught = false;
        try {
            PayByLinkService::edit($this->payByLinkId)
                ->withAmount(null)
                ->withPayByLinkData($this->payByLink)
                ->withDescription('Update PayByLink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayByLink_RandomPayByLinkId()
    {
        $exceptionCaught = false;
        try {
            PayByLinkService::edit(GenerationUtils::getGuid())
                ->withAmount($this->amount)
                ->withPayByLinkData($this->payByLink)
                ->withDescription('Update PayByLink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40108', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - You cannot update a link that has a 400 status', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindPayByLinkByStatus()
    {
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYBYLINK_STATUS, PayByLinkStatus::EXPIRED)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
        $this->assertEquals(PayByLinkStatus::EXPIRED, $randomPayByLink->status);
    }

    public function testFindPayByLinkUsageModeAndName()
    {
        $name = 'iphone 14';
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYMENT_METHOD_USAGE_MODE, PaymentMethodUsageMode::SINGLE)
            ->andWith(SearchCriteria::DISPLAY_NAME, $name)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
        $this->assertEquals(PaymentMethodUsageMode::SINGLE, $randomPayByLink->usageMode);
        $this->assertEquals($name, $randomPayByLink->name);
    }

    public function testFindPayByLinkByAmount()
    {
        $amount = 2.11;
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::AMOUNT, $amount)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
        $this->assertEquals($amount, $randomPayByLink->amount);
    }

    public function testFindPayByLinkByExpireDate()
    {
        $date = new DateTime('+1month');
        $response = PayByLinkService::findPayByLink(1, 10)
            ->orderBy(PayByLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::EXPIRATION_DATE, $date)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayByLinkSummary $randomPayByLink */
        $randomPayByLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayByLink);
        $this->assertInstanceOf(PayByLinkSummary::class, $randomPayByLink);
        $this->assertEquals($date->format('Y-m-d'), $randomPayByLink->expirationDate->format('Y-m-d'));
    }

    private function assertPayByLinkResponse(Transaction $response): void
    {
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(PayByLinkStatus::ACTIVE, $response->responseMessage);
        $this->assertEquals($this->amount, $response->balanceAmount);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
    }

    private function setupTransactionConfig(): GpApiConfig
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }
}