<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\MerchantAccountsSortProperty;
use GlobalPayments\Api\Entities\Enums\MerchantAccountStatus;
use GlobalPayments\Api\Entities\Enums\MerchantAccountType;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\FundsData;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\FundsAccountDetails;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\ThreeDSecureAcsClient;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class PartnershipModeTest extends TestCase
{
    /** @var CreditCardData */
    private CreditCardData $card;
    /** @var string */
    private string $currency;

    /** @var GpApiConfig */
    private GpApiConfig $baseConfig;
    private float $amount;

    /** @var Address */
    private Address $shippingAddress;

    /** @var BrowserData */
    private BrowserData $browserData;

    private string $merchantId;

    private \DateTime $startDate;

    public function setup(): void
    {
        $this->baseConfig = $this->setUpConfig();
        ServicesContainer::configureService($this->baseConfig);

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
        $this->currency = 'USD';
        $this->amount = '10.01';

        $this->startDate = (new \DateTime())->modify('-3 year');

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

        $merchants = ReportingService::findMerchants(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->andWith(SearchCriteria::START_DATE, $this->startDate)
            ->execute();

        if (count($merchants->result) > 0) {
            $this->merchantId = reset($merchants->result)->id;
            $this->setUpConfigMerchant();
        } else {
            $this->tearDownAfterClass();
            $this->markTestSkipped("Merchant ID not found!");
        }
    }

    private function setUpConfigMerchant(): void
    {
        $config = clone $this->baseConfig;
        $config->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->merchantContactUrl = 'https://enp4qhvjseljg.x.pipedream.net/';
        $config->merchantId = $this->merchantId;

        $accounts = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(DataServiceCriteria::MERCHANT_ID, $this->merchantId)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->andWith(SearchCriteria::START_DATE, $this->startDate)
            ->execute();

        $transactionAccounts = array_filter(
            $accounts->result,
            function ($account) {
                return (
                    $account->type == MerchantAccountType::TRANSACTION_PROCESSING &&
                    in_array(PaymentMethodName::CARD, $account->paymentMethods)
                );
            }
        );

        $config->accessTokenInfo->transactionProcessingAccountID =
            (count($transactionAccounts) > 0 ? reset($transactionAccounts)->id : null);

        $configName = 'config_' . $this->merchantId;
        ServicesContainer::configureService($config, $configName);

        $configAch = clone $config;
        $configAch->accessTokenInfo = new AccessTokenInfo();
        $transactionAccounts = array_filter(
            $accounts->result,
            function ($account) {
                return (
                    $account->type == MerchantAccountType::TRANSACTION_PROCESSING &&
                    in_array("BANK_TRANSFER", $account->paymentMethods)
                );
            }
        );

        $configAch->accessTokenInfo->transactionProcessingAccountID =
            (count($transactionAccounts) > 0 ? end($transactionAccounts)->id : null);

        $configName = 'config_ACH_' . $this->merchantId;
        ServicesContainer::configureService($configAch, $configName);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::PARTNER_SOLUTION_APP_ID;
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::PARTNER_SOLUTION_APP_KEY;

        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testCreditSaleWithPartnerMode()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $response = $this->card->charge(69)
            ->withCurrency($this->currency)
            ->withAddress($address)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditSaleRefundWithPartnerMode()
    {
        $response = $this->card->charge(11)
            ->withCurrency($this->currency)
            ->withAddress($this->shippingAddress)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $refundResponse = $response->refund(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($refundResponse);
        $this->assertEquals('SUCCESS', $refundResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $refundResponse->responseMessage);
        unset($config);
    }

    public function testCreditRefundWithPartnerMode()
    {
        $refundResponse = $this->card->refund(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($refundResponse);
        $this->assertEquals('SUCCESS', $refundResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $refundResponse->responseMessage);
        unset($config);
    }

    public function testCreditAuthAndCaptureWithPartnerMode()
    {
        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $captureResponse = $authResponse->capture(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($captureResponse);
        $this->assertEquals('SUCCESS', $captureResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureResponse->responseMessage);

        unset($config);
    }

    public function testCreditAuthAndReverseWithPartnerMode()
    {
        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $reverseResponse = $authResponse->reverse(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('SUCCESS', $reverseResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverseResponse->responseMessage);
        unset($config);
    }

    public function testCreditReAuthWithPartnerMode()
    {
        $authResponse = $this->card->authorize(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($authResponse);
        $this->assertEquals('SUCCESS', $authResponse->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $authResponse->responseMessage);

        $reverseResponse = $authResponse->reverse(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('SUCCESS', $reverseResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverseResponse->responseMessage);

        $reAuthResponse = $reverseResponse->reauthorized(11)
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($reAuthResponse);
        $this->assertEquals('SUCCESS', $reAuthResponse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reAuthResponse->responseMessage);
        unset($config);
    }

    public function testFullCycle3DSChallenge_v2_PartnerMode()
    {
        $this->card->number = '4222000001227408';

        $secureEcom = Secure3dService::checkEnrollment($this->card)
            ->withCurrency($this->currency)
            ->withAmount($this->amount)
            ->execute('config_' . $this->merchantId);

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
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($initAuth);
        $this->assertEquals(Secure3dStatus::CHALLENGE_REQUIRED, $initAuth->status);
        $this->assertNotNull($initAuth->issuerAcsUrl);
        $this->assertNotNull($initAuth->payerAuthenticationRequest);

        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authClient->setGatewayProvider($this->baseConfig->getGatewayProvider());
        $authResponse = $authClient->authenticate_v2($initAuth);
        $this->assertTrue($authResponse->getStatus());
        $this->assertNotEmpty($authResponse->getMerchantData());

        $secureEcom = Secure3dService::getAuthenticationData()
            ->withServerTransactionId($authResponse->getMerchantData())
            ->execute('config_' . $this->merchantId);
        $this->card->threeDSecure = $secureEcom;

        $this->assertEquals(Secure3dStatus::SUCCESS_AUTHENTICATED, $secureEcom->status);
        $this->assertEquals('YES', $secureEcom->liabilityShift);

        $response = $this->card->charge($this->amount)->withCurrency($this->currency)->execute('config_' . $this->merchantId);
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testVerifyTokenizedPaymentMethodWithPartnerMode()
    {
        $this->markTestSkipped('Missing TKA_ account from the merchant');
        $response = $this->card->tokenize()->execute('config_' . $this->merchantId);
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;

        $response = $tokenizedCard->verify()
            ->withCurrency($this->currency)
            ->execute('config_' . $this->merchantId);

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCreditSaleWithPartnerMode_WrongMerchant()
    {
        $merchantId = 'MER_' . GenerationUtils::getGuid();

        $config = clone($this->baseConfig);
        $config->merchantId = $merchantId;
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
        $config->accessTokenInfo->transactionProcessingAccountID = '123566789';
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
            $this->assertStringContainsString(
                'Merchant configuration does not exist for the following combination', $e->getMessage()
            );
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

    /**
     * Split and reversal are available only for Partner mode or on FundsAccount payment method
     */
    public function testSplitAndReversalTransfer()
    {
        $eCheck = new ECheck();
        $eCheck->accountNumber = '1234567890';
        $eCheck->routingNumber = '122000030';
        $eCheck->accountType = AccountType::SAVINGS;
        $eCheck->secCode = SecCode::WEB;
        $eCheck->checkReference = '123';
        $eCheck->merchantNotes = '123';
        $eCheck->bankName = 'First Union';
        $eCheck->checkHolderName = 'Jane Doe';

        $address = new Address();
        $address->streetAddress1 = "Apartment 852";
        $address->streetAddress2 = "Complex 741";
        $address->streetAddress3 = "no";
        $address->city = "Chicago";
        $address->postalCode = "5001";
        $address->state = "IL";
        $address->countryCode = "US";

        $customer = new Customer();
        $customer->key = "e193c21a-ce64-4820-b5b6-8f46715de931";
        $customer->firstName = "James";
        $customer->lastName = "Mason";
        $customer->dateOfBirth = "1980-01-01";
        $customer->mobilePhone = new PhoneNumber('+35', '312345678', PhoneNumberType::MOBILE);
        $customer->homePhone = new PhoneNumber('+1', '12345899', PhoneNumberType::HOME);

        $transaction = $eCheck->charge(10)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCustomerData($customer)
            ->execute('config_ACH_' . $this->merchantId);

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $fundsData = new FundsData();
        $fundsData->recipientAccountId ='FMA_88f20a1a45814a1098873cd19bdc383d';
        $transferAmount = '1';
        $transferReference = 'split identifier';
        $transferDescription = 'Split 1';
        $split = $transaction->split($transferAmount)
            ->withDescription($transferDescription)
            ->withReference($transferReference)
            ->withFundsData($fundsData)
            ->execute('config_ACH_' . $this->merchantId);

        $this->assertNotNull($split);
        $this->assertEquals('SUCCESS', $split->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $split->responseMessage);
        $this->assertNotNull($split->transfersFundsAccount);
        /** @var FundsAccountDetails $transfer */
        $transfer = $split->transfersFundsAccount->getIterator()->current();
        $this->assertEquals('SUCCESS', $transfer->status);
        $this->assertEquals($transferAmount, $transfer->amount);
        $this->assertEquals($transferReference, $transfer->reference);
        $this->assertEquals($transferDescription, $transfer->description);

        $trfTransaction = Transaction::fromId($transfer->id, null , PaymentMethodType::ACCOUNT_FUNDS);
        $reverseTrf = $trfTransaction->reverse()
            ->execute('config_ACH_' . $this->merchantId);

        $this->assertEquals('SUCCESS', $reverseTrf->responseCode);
        $this->assertEquals(TransactionStatus::FUNDED, $reverseTrf->responseMessage);
        $this->assertEquals($transferAmount, $reverseTrf->balanceAmount);
        $this->assertEquals($transferReference, $reverseTrf->clientTransactionId);
    }
}