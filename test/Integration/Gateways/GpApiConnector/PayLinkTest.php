<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\PayLinkSortProperty;
use GlobalPayments\Api\Entities\Enums\PayLinkStatus;
use GlobalPayments\Api\Entities\Enums\PayLinkType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\PayLinkData;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\PayLinkSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\PayLinkService;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Data\GpApi3DSTestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class PayLinkTest extends TestCase
{
    private $startDate;
    private $endDate;
    private $amount = 2.11;
    private $payLink;
    private $card;
    private $shippingAddress;
    private $browserData;
    private $payLinkId;

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setup() : void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $this->payLink = new PayLinkData();
        $this->payLink->type = PayLinkType::PAYMENT;
        $this->payLink->usageMode = PaymentMethodUsageMode::SINGLE;
        $this->payLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $this->payLink->usageLimit = 3;
        $this->payLink->name = 'Mobile Bill Payment';
        $this->payLink->isShippable = true;
        $this->payLink->shippingAmount = 1.23;
        $this->payLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $this->payLink->images = [];
        $this->payLink->returnUrl = 'https://www.example.com/returnUrl';
        $this->payLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $this->payLink->cancelUrl = 'https://www.example.com/returnUrl';

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

        $response = PayLinkService::findPayLink(1, 1)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYLINK_STATUS, PayLinkStatus::ACTIVE)
            ->execute();
        if (count($response->result) == 1) {
            $this->payLinkId = $response->result[0]->id;
        }
    }


    public function setUpConfig()
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

    public function testReportPayLinkDetail()
    {
        $paylinkId = 'LNK_GderFbTKFzj7X507E7OgDfuRnlKViP';
        /** @var PayLinkSummary $response */
        $response = PayLinkService::payLinkDetail($paylinkId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(PayLinkSummary::class, $response);
        $this->assertEquals($paylinkId, $response->id);
    }

    public function testReportPayLinkDetail_RandomId()
    {
        $paylinkId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            PayLinkService::payLinkDetail($paylinkId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals("Status Code: RESOURCE_NOT_FOUND - Links " . $paylinkId . " not found at this /ucp/links/" . $paylinkId . "", $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindPayLinkByDate()
    {
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
    }

    public function testCreatePayLink()
    {
        $payLink = new PayLinkData();
        $payLink->type = PayLinkType::PAYMENT;
        $payLink->usageMode = PaymentMethodUsageMode::SINGLE;
        $payLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $payLink->usageLimit = 1;
        $payLink->name = 'Mobile Bill Payment';
        $payLink->isShippable = true;
        $payLink->shippingAmount = 1.23;
        $payLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $payLink->images = [];
        $payLink->returnUrl = 'https://www.example.com/returnUrl';
        $payLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $payLink->cancelUrl = 'https://www.example.com/returnUrl';

        $response = PayLinkService::create($payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);
        $this->assertEquals("YES", $response->payLinkResponse->isShippable);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));
    }

    public function testCreatePayLink_MultipleUsage()
    {
        $payLink = new PayLinkData();
        $payLink->type = PayLinkType::PAYMENT;
        $payLink->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $payLink->allowedPaymentMethods = [PaymentMethodName::CARD];
        $payLink->usageLimit = 2;
        $payLink->name = 'Mobile Bill Payment';
        $payLink->isShippable = true;
        $payLink->shippingAmount = 1.23;
        $payLink->expirationDate = date('Y-m-d H:i:s', strtotime(' + 10 days'));//date('Y-m-d H:i:s') + 10;
        $payLink->images = [];
        $payLink->returnUrl = 'https://www.example.com/returnUrl';
        $payLink->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $payLink->cancelUrl = 'https://www.example.com/returnUrl';

        $response = PayLinkService::create($payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));
    }

    public function testCreatePayLink_ThenCharge()
    {
        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);
        $this->assertEquals("YES", $response->payLinkResponse->isShippable);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $transaction = $this->card->charge($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayLinkService::payLinkDetail($response->payLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayLinkSummary::class, $getResponse);
        $this->assertEquals($response->payLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayLink_MultipleUsage_ThenCharge()
    {
        $this->payLink->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $this->payLink->usageLimit = 2;

        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        for ($i = 1; $i <= $this->payLink->usageLimit; $i++) {
            $transaction = $this->card->charge($this->amount)
                ->withCurrency('GBP')
                ->withPaymentLinkId($response->payLinkResponse->id)
                ->execute("createTransaction");

            $this->assertNotNull($transaction);
            $this->assertEquals('SUCCESS', $transaction->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
        }

        sleep(2);

        $getResponse = PayLinkService::payLinkDetail($response->payLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayLinkSummary::class, $getResponse);
        $this->assertEquals($response->payLinkResponse->id, $getResponse->id);
        $this->assertCount($this->payLink->usageLimit, $getResponse->transactions);
        $this->assertEquals(0, intval($getResponse->viewedCount));
        $this->assertEquals(0, intval($getResponse->usageCount));
    }

    public function testCreatePayLink_ThenAuthorizeAndCapture()
    {
        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $authorize = $this->card->authorize($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payLinkResponse->id)
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

        $getResponse = PayLinkService::payLinkDetail($response->payLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayLinkSummary::class, $getResponse);
        $this->assertEquals($response->payLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayLink_ThenCharge_WithTokenizedCard()
    {
        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));

        ServicesContainer::configureService($this->setupTransactionConfig(), "createTransaction");

        $tokenResponse = $this->card->tokenize(true, PaymentMethodUsageMode::SINGLE)
            ->execute("createTransaction");
        $tokenId = $tokenResponse->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $transaction = $tokenizedCard->charge($this->amount)
            ->withCurrency('GBP')
            ->withPaymentLinkId($response->payLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayLinkService::payLinkDetail($response->payLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayLinkSummary::class, $getResponse);
        $this->assertEquals($response->payLinkResponse->id, $getResponse->id);
    }

    public function testCreatePayLink_ThenCharge_With3DS()
    {
        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);

        fwrite(STDERR, print_r($response->payLinkResponse->url, TRUE));

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
            ->withPaymentLinkId($response->payLinkResponse->id)
            ->execute("createTransaction");

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        sleep(2);

        $getResponse = PayLinkService::payLinkDetail($response->payLinkResponse->id)
            ->execute();

        $this->assertNotNull($getResponse);
        $this->assertInstanceOf(PayLinkSummary::class, $getResponse);
        $this->assertEquals($response->payLinkResponse->id, $getResponse->id);
    }

    public function testEditPayLink()
    {
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYLINK_STATUS, PayLinkStatus::ACTIVE)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
        $this->assertNotNull($randomPayLink->id);

        $payLinkData = new PayLinkData();
        $payLinkData->name = 'bla bla bla';
        $payLinkData->usageMode = PaymentMethodUsageMode::MULTIPLE;
        $payLinkData->type = PayLinkType::PAYMENT;
        $payLinkData->usageLimit = 5;
        $payLinkData->isShippable = false;
        $amount = 10.08;
        $response = PayLinkService::edit($randomPayLink->id)
            ->withAmount($amount)
            ->withPayLinkData($payLinkData)
            ->withDescription('Update Paylink description')
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(PayLinkStatus::ACTIVE, $response->responseMessage);
        $this->assertEquals($amount, $response->balanceAmount);
        $this->assertNotNull($response->payLinkResponse->url);
        $this->assertNotNull($response->payLinkResponse->id);
    }

    public function testCreatePayLink_MissingType()
    {
        $this->payLink->type = null;

        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingUsageMode()
    {
        $this->payLink->usageMode = null;

        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingPaymentMethod()
    {
        $this->payLink->allowedPaymentMethods = null;

        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingName()
    {
        $this->payLink->name = null;

        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingShippable()
    {
        $this->payLink->isShippable = null;

        $response = PayLinkService::create($this->payLink, $this->amount)
            ->withCurrency('GBP')
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('March and April Invoice')
            ->execute();

        $this->assertPayLinkResponse($response);
        $this->assertEquals("NO", $response->payLinkResponse->isShippable);
    }

    public function testCreatePayLink_MissingShippingAmount()
    {
        $this->payLink->shippingAmount = null;

        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingDescription()
    {
        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testCreatePayLink_MissingCurrency()
    {
        $exceptionCaught = false;
        try {
            PayLinkService::create($this->payLink, $this->amount)
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

    public function testEditPayLink_MissingUsageMode()
    {
        $this->assertNotNull($this->payLinkId);
        $this->payLink->usageMode = null;

        $exceptionCaught = false;
        try {
            PayLinkService::edit($this->payLinkId)
                ->withAmount($this->amount)
                ->withPayLinkData($this->payLink)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('usageMode cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayLink_MissingType()
    {
        $this->assertNotNull($this->payLinkId);
        $this->payLink->type = null;

        $exceptionCaught = false;
        try {
            PayLinkService::edit($this->payLinkId)
                ->withAmount($this->amount)
                ->withPayLinkData($this->payLink)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('type cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayLink_MissingUsageLimit()
    {
        $this->assertNotNull($this->payLinkId);
        $this->payLink->usageLimit = null;

        $exceptionCaught = false;
        try {
            PayLinkService::edit($this->payLinkId)
                ->withAmount($this->amount)
                ->withPayLinkData($this->payLink)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('usageLimit cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayLink_MissingPayLinkData()
    {
        $this->assertNotNull($this->payLinkId);
        $exceptionCaught = false;
        try {
            PayLinkService::edit($this->payLinkId)
                ->withAmount($this->amount)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Property `usageMode` does not exist on `GlobalPayments\Api\Builders\ManagementBuilder`', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayLink_MissingAmount()
    {
        $this->assertNotNull($this->payLinkId);
        $exceptionCaught = false;
        try {
            PayLinkService::edit($this->payLinkId)
                ->withAmount(null)
                ->withPayLinkData($this->payLink)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayLink_RandomPayLinkId()
    {
        $exceptionCaught = false;
        try {
            PayLinkService::edit(GenerationUtils::getGuid())
                ->withAmount($this->amount)
                ->withPayLinkData($this->payLink)
                ->withDescription('Update Paylink description')
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40108', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - You cannot update a link that has a 400 status', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindPayLinkByStatus()
    {
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYLINK_STATUS, PayLinkStatus::EXPIRED)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
        $this->assertEquals(PayLinkStatus::EXPIRED, $randomPayLink->status);
    }

    public function testFindPayLinkUsageModeAndName()
    {
        $name = 'iphone 14';
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYMENT_METHOD_USAGE_MODE, PaymentMethodUsageMode::SINGLE)
            ->andWith(SearchCriteria::DISPLAY_NAME, $name)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
        $this->assertEquals(PaymentMethodUsageMode::SINGLE, $randomPayLink->usageMode);
        $this->assertEquals($name, $randomPayLink->name);
    }

    public function testFindPayLinkByAmount()
    {
        $amount = 10.00;
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::AMOUNT, $amount)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
        $this->assertEquals($amount, $randomPayLink->amount);
    }
    public function testFindPayLinkByExpireDate()
    {
        $date = new \DateTime('2024-05-09');
        $response = PayLinkService::findPayLink(1, 10)
            ->orderBy(PayLinkSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::EXPIRATION_DATE, $date)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var PayLinkSummary $randomPayLink */
        $randomPayLink = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomPayLink);
        $this->assertInstanceOf(PayLinkSummary::class, $randomPayLink);
        $this->assertEquals($date->format('Y-m-d'), $randomPayLink->expirationDate->format('Y-m-d'));
    }

    private function assertPayLinkResponse(Transaction $response)
    {
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(PayLinkStatus::ACTIVE, $response->responseMessage);
        $this->assertEquals($this->amount, $response->balanceAmount);
        $this->assertNotNull($response->payLinkResponse->url);
        $this->assertNotNull($response->payLinkResponse->id);
    }

    private function setupTransactionConfig()
    {
        $configTrn = new GpApiConfig();
        $configTrn->appId = 'oDVjAddrXt3qPJVPqQvrmgqM2MjMoHQS';
        $configTrn->appKey = 'DHUGdzpjXfTbjZeo';
        $configTrn->channel = Channel::CardNotPresent;
        $configTrn->country = 'GB';
        $configTrn->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $configTrn->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $configTrn->merchantContactUrl = 'https://enp4qhvjseljg.x.pipedream.net/';
        $configTrn->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $configTrn;
    }
}