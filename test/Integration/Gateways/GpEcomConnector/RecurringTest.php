<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\RecurringService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RecurringTest extends TestCase
{

    /** @var $newCustomer */
    public $newCustomer;

    /** @var $card */
    public $card;

    public function getCustomerId()
    {
        return sprintf("%s-Realex", (new \DateTime())->format("Ymd"));
    }

    public function getPaymentId($type)
    {
        return sprintf("%s-Realex-%s", (new \DateTime())->format("Ymd"), $type);
    }

    public function getScheduleId($type)
    {
        return sprintf("%s-Realex-%s", (new \DateTime())->format("Ymd"), $type);
    }

    protected function config()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "3dsecure";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        $config->channel = 'ECOM';
        return $config;
    }

    protected function dccSetup()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "apidcc";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";

        ServicesContainer::configureService($config);
    }

    public function setup(): void
    {
        ServicesContainer::configureService($this->config());

        $this->newCustomer = new Customer();
        $this->newCustomer->key = $this->getCustomerId();
        $this->newCustomer->title = "Mr.";
        $this->newCustomer->firstName = "James";
        $this->newCustomer->lastName = "Mason";
        $this->newCustomer->company = "Realex Payments";
        $this->newCustomer->address = new Address();
        $this->newCustomer->address->streetAddress1 = "Flat 123";
        $this->newCustomer->address->streetAddress2 = "House 456";
        $this->newCustomer->address->streetAddress3 = "The Cul-De-Sac";
        $this->newCustomer->address->city = "Halifax";
        $this->newCustomer->address->province = "West Yorkshire";
        $this->newCustomer->address->postalCode = "W6 9HR";
        $this->newCustomer->address->country = "United Kingdom";
        $this->newCustomer->homePhone = "+35312345678";
        $this->newCustomer->workPhone = "+3531987654321";
        $this->newCustomer->fax = "+124546871258";
        $this->newCustomer->mobilePhone = "+25544778544";
        $this->newCustomer->email = "text@example.com";
        $this->newCustomer->comments = "Campaign Ref E7373G";

        $this->card = new CreditCardData();
        $this->card->number = "4012001037141112";
        $this->card->expMonth = 10;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'James Mason';
    }

    /* 08. Card Storage Create Payer */
    /* Request Type: payer-new  */

    public function testcardStorageCreatePayer()
    {
        try {
            $response = $this->newCustomer->Create();
            $this->assertNewCustomer($response);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
            $this->assertEquals(
                sprintf('This Payer Ref [%s] has already been used - please use another one', $this->newCustomer->key),
                $exc->responseMessage);
        }
    }

    /* 09. Card Storage Store Card */
    /* Request Type: card-new  */

    public function testcardStorageStoreCard()
    {
        $card = new CreditCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '123';
        $card->cardHolderName = 'James Mason';

        try {
            $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId("Credit"), $card)->create();
            $this->assertNewRecurringPaymentMethod($this->getPaymentId("Credit"), $paymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
            $this->assertStringContainsString(
                sprintf('This Card Ref [%s] has already been used', $this->getPaymentId("Credit")),
                $exc->responseMessage
            );
        }
    }

    /* 09.01 Card Storage Store Card with Stored Credential */
    /* Request Type: card-new  */

    public function testcardStorageStoreCardWithStoredCredential()
    {
        $card = new CreditCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '123';
        $card->cardHolderName = 'James Mason';

        $storedCredential = new StoredCredential();
        $storedCredential->schemeId = 'YOUR_DESIRED_SCHEME_ID';
        $paymentId = sprintf("%s-RealexStoredCredential-%s", (new \DateTime())->format("Ymd"), 'Credit');
        try {
            $paymentMethod = $this->newCustomer
                ->addPaymentMethod($paymentId, $card, $storedCredential)
                ->create();

            $this->assertNewRecurringPaymentMethod($paymentId, $paymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
            $this->assertStringContainsString(
                sprintf('This Card Ref [%s] has already been used', $paymentId),
                $exc->responseMessage
            );
        }
    }

    /* 10. Card Storage Charge Card */
    /* Request Type: receipt-in  */

    public function testcardStorageChargeCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $response = $paymentMethod->charge(10)
            ->withCurrency("EUR")
            ->withCvn("123")
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /* 11. CardStorage ThreeDSecure Verify Enrolled */
    /* Request Type: realvault-3ds-verifyenrolled */

    public function testcardStorageThreeDSecureVerifyEnrolled()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        $response = $paymentMethod->verify()
            ->withAmount(10)
            ->withCurrency('USD')
            ->withModifier(TransactionModifier::SECURE3D)
            ->execute();

        // get the response details to update the DB
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /* 12. CardStorage Dcc Rate Lookup */
    /* Request Type: realvault-dccrate */

    public function testcardStorageDccRateLookup()
    {
        $this->dccSetup();

        $orderId = GenerationUtils::generateOrderId();
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $dccDetails = $paymentMethod->getDccRate(DccRateType::SALE, DccProcessor::FEXCO)
            ->withAmount(1001)
            ->withCurrency('EUR')
            ->withOrderId($orderId)
            ->execute();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);
    }

    /* 14. CardStorage UpdatePayer */
    /* Request Type: payer-edit */

    public function testcardStorageUpdatePayer()
    {
        $customer = new Customer();
        $customer->key = $this->getCustomerId();
        $customer->firstName = "Perry";

        $customerUpdated = $customer->saveChanges();

        $this->assertNotNull($customerUpdated);
        $this->assertNotNull($customerUpdated->id);
        $this->assertEquals($customer->key, $customerUpdated->key);
    }

    /* 15. CardStorage Continuous Authority First */
    /* Request Type: auth */

    public function testContinuousAuthorityFirst()
    {
        // create the card object
        $card = new CreditCardData();
        $card->number = '5425230000004415';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';


        // process an auto-settle authorization
        $response = $card->charge(15)
            ->withCurrency("EUR")
            ->withRecurringInfo(RecurringType::VARIABLE, RecurringSequence::FIRST)
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the details to save to the DB for future Transaction Management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId;

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 15. CardStorage Continuous Authority Subsequent */
    /* Request Type: receipt-in */

    public function testContinuousAuthoritySubsequent()
    {
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        // charge the stored card/payment method
        $response = $paymentMethod->charge(15)
            ->withCurrency("EUR")
            ->withCvn("123")
            ->withRecurringInfo(RecurringType::VARIABLE, RecurringSequence::SUBSEQUENT)
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 15. CardStorage Continuous Authority Last */
    /* Request Type: receipt-in */

    public function testContinuousAuthorityLast()
    {
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        // charge the stored card/payment method
        $response = $paymentMethod->charge(15)
            ->withCurrency("EUR")
            ->withCvn("123")
            ->withRecurringInfo(RecurringType::VARIABLE, RecurringSequence::LAST)
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 16. Card Storage Refund */
    /* Request Type: payment-out */

    public function testcardStorageRefund()
    {
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        // charge the stored card/payment method
        $response = $paymentMethod->refund(10)
            ->withCurrency("EUR")
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 17. Card Storage UpdateCard */
    /* Request Type: card-update-card */

    public function testcardStorageUpdateCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        $paymentMethod->paymentMethod = new CreditCardData();
        $paymentMethod->paymentMethod->number = "5425230000004415";
        $paymentMethod->paymentMethod->expMonth = 10;
        $paymentMethod->paymentMethod->expYear = TestCards::validCardExpYear();
        $paymentMethod->paymentMethod->cardHolderName = "Philip Marlowe";

        $response = $paymentMethod->SaveChanges();

        $this->assertTrue($response instanceof RecurringPaymentMethod);
        $this->assertNotNull($response->id);
        $this->assertEquals($this->getPaymentId("Credit"), $response->key);
    }

    /* 17.01 Card Storage UpdateCard with StoredCredential */
    /* Request Type: card-update-card */

    public function testcardStorageUpdateCardWithStoredCredential()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $storedCredential = new StoredCredential();
        $storedCredential->schemeId = 'YOUR_DESIRED_SCHEME_ID';
        $paymentMethod->storedCredential = $storedCredential;

        $paymentMethod->paymentMethod = new CreditCardData();
        $paymentMethod->paymentMethod->number = "5425230000004415";
        $paymentMethod->paymentMethod->expMonth = 10;
        $paymentMethod->paymentMethod->expYear = TestCards::validCardExpYear();
        $paymentMethod->paymentMethod->cardHolderName = "Philip Marlowe";

        $response = $paymentMethod->SaveChanges();

        $this->assertTrue($response instanceof RecurringPaymentMethod);
        $this->assertNotNull($response->id);
        $this->assertEquals($this->getPaymentId("Credit"), $response->key);
    }

    /* 18. Card Storage Verify Card */
    /* Request Type: receipt-in-otb */

    public function testcardStorageVerifyCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        // verify the stored card/payment method is valid and active
        $response = $paymentMethod->verify()
            ->withCvn("123")
            ->execute();

        // get the response details to update the DB
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 13. CardStorage DeleteCard */
    /* Request Type: card-cancel-card */

    public function testcardStorageDeleteCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        // delete the stored card/payment method
        // WARNING! This can't be undone
        $response = $paymentMethod->delete();

        $this->assertNotNull($response);
        $this->assertNotNull($response->id);
        $this->assertEquals($this->getPaymentId("Credit"), $response->key);
    }

    /* Request Type: receipt-in  */

    public function testcardStorageChargeCardDCC()
    {
        $this->dccSetup();
        $this->testcardStorageCreatePayer();
        $this->testcardStorageStoreCard();

        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));

        $orderId = GenerationUtils::generateOrderId();
        $dccDetails = $paymentMethod->getDccRate(DccRateType::SALE, DccProcessor::FEXCO)
            ->withAmount(1001)
            ->withCurrency('EUR')
            ->withOrderId($orderId)
            ->execute();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $response = $paymentMethod->charge(1001)
            ->withCurrency("EUR")
            ->withCvn("123")
            ->withDccRateData($dccDetails->dccRateData)
            ->withOrderId($orderId)
            ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /***************************************************
     *             Payment Scheduler tests             *
     ***************************************************/
    /**
     * @param Customer $customer
     */
    private function assertNewCustomer(Customer $customer)
    {
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->key);
        $this->assertEquals($this->newCustomer->id, $customer->id);
    }

    private function assertNewRecurringPaymentMethod($paymentMethodId, RecurringPaymentMethod $recurringPaymentMethod)
    {
        $this->assertNotNull($recurringPaymentMethod);
        $this->assertNotNull($recurringPaymentMethod->id);
        $this->assertEquals($paymentMethodId, $recurringPaymentMethod->key);
    }

    private function assertNewPaymentSchedule($scheduleKey, $schedule)
    {
        $this->assertNotNull($schedule);
        $this->assertEquals($scheduleKey, $schedule->key);
        $this->assertNotNull($schedule->id);
    }

    public function testCardStorageAddScheduleX()
    {
        try {
            $customer = $this->newCustomer->create();
            $this->assertNewCustomer($customer);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);

        try {
            $paymentMethod = $paymentMethod->create();
            $this->assertNewRecurringPaymentMethod($this->getPaymentId('Credit'), $paymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $scheduleKey = GenerationUtils::generateScheduleId();
        $schedule = $paymentMethod->addSchedule($scheduleKey)
            ->withStartDate(new \DateTime())
            ->withAmount(30.01)
            ->withCurrency('USD')
            ->withFrequency(ScheduleFrequency::SEMI_ANNUALLY)
            ->withReprocessingCount(1)
            ->withnumberOfPaymentsRemaining(12)
            ->withCustomerNumber('E8953893489')
            ->withOrderPrefix('gym')
            ->withName('Gym Membership')
            ->withDescription('Social Sign-Up')
            ->create();

        $this->assertNewPaymentSchedule($scheduleKey, $schedule);

        /** @var Schedule $schedule */
        $schedule = RecurringService::get($schedule);

        $this->assertEquals($scheduleKey, $schedule->key);
        $this->assertEquals(12, $schedule->numberOfPaymentsRemaining);
        $this->assertEquals(ScheduleFrequency::SEMI_ANNUALLY, $schedule->frequency);
    }

    public function testCardStorageAddSchedule_AllFrequency()
    {
        try {
            $customer = $this->newCustomer->create();
            $this->assertNewCustomer($customer);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);

        try {
            $paymentMethod = $paymentMethod->create();
            $this->assertNewRecurringPaymentMethod($this->getPaymentId('Credit'), $paymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $transactionStatus = new ScheduleFrequency();
        $reflectionClass = new ReflectionClass($transactionStatus);
        foreach ($reflectionClass->getConstants() as $frequency) {
            if ($frequency == ScheduleFrequency::BI_WEEKLY || $frequency == ScheduleFrequency::SEMI_MONTHLY) {
                continue;
            }
            $scheduleKey = GenerationUtils::generateScheduleId();
            $schedule = $paymentMethod->addSchedule($scheduleKey)
                ->withStartDate(new \DateTime())
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency($frequency)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();

            $this->assertNewPaymentSchedule($scheduleKey, $schedule);

            /** @var Schedule $schedule */
            $schedule = RecurringService::get($schedule);

            $this->assertEquals($scheduleKey, $schedule->key);
            $this->assertEquals(12, $schedule->numberOfPaymentsRemaining);
            $this->assertEquals($frequency, $schedule->frequency);
        }
    }

    public function testCardStorageAddSchedule_WithIndefinitelyRun()
    {
        try {
            $customer = $this->newCustomer->create();
            $this->assertNewCustomer($customer);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        try {
            $recurringPaymentMethod = $paymentMethod->create();
            $this->assertNewRecurringPaymentMethod($paymentMethod->id, $recurringPaymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $scheduleKey = GenerationUtils::generateScheduleId();
        $schedule = $paymentMethod->addSchedule($scheduleKey)
            ->withStartDate(new \DateTime())
            ->withAmount(30.01)
            ->withCurrency('USD')
            ->withFrequency(ScheduleFrequency::QUARTERLY)
            ->withReprocessingCount(1)
            ->withnumberOfPaymentsRemaining(-1)
            ->withCustomerNumber('E8953893489')
            ->withOrderPrefix('gym')
            ->withName('Gym Membership')
            ->withDescription('Social Sign-Up')
            ->create();

        $this->assertNewPaymentSchedule($scheduleKey, $schedule);

        /** @var Schedule $schedule */
        $schedule = RecurringService::get($schedule);

        $this->assertEquals($scheduleKey, $schedule->key);
        $this->assertEquals(-1, $schedule->numberOfPaymentsRemaining);
        $this->assertEquals(ScheduleFrequency::QUARTERLY, $schedule->frequency);
    }

    public function testCardStorageAddSchedule_With999Runs()
    {
        try {
            $customer = $this->newCustomer->create();
            $this->assertNewCustomer($customer);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        try {
            $recurringPaymentMethod = $paymentMethod->create();
            $this->assertNewRecurringPaymentMethod($paymentMethod->id, $recurringPaymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
        }

        $scheduleKey = GenerationUtils::generateScheduleId();
        $schedule = $paymentMethod->addSchedule($scheduleKey)
            ->withStartDate(new \DateTime())
            ->withAmount(30.01)
            ->withCurrency('USD')
            ->withFrequency(ScheduleFrequency::QUARTERLY)
            ->withReprocessingCount(1)
            ->withnumberOfPaymentsRemaining(999)
            ->withCustomerNumber('E8953893489')
            ->withOrderPrefix('gym')
            ->withName('Gym Membership')
            ->withDescription('Social Sign-Up')
            ->create();

        $this->assertNewPaymentSchedule($scheduleKey, $schedule);

        $schedule = RecurringService::get($schedule);

        $this->assertEquals($scheduleKey, $schedule->id);
        $this->assertEquals(999, $schedule->numberOfPaymentsRemaining);
        $this->assertEquals(ScheduleFrequency::QUARTERLY, $schedule->frequency);
    }

    public function testCardStorageAddSchedule_WithoutScheduleRef()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule(null)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 502 - Mandatory Fields missing: [/request/scheduleref]. See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutFrequency()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 502 - Mandatory Fields missing: [/request/schedule]. See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutCustomerRef()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        $paymentMethod->customerKey = null;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 502 - Mandatory Fields missing: [/request/payerref]. See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutPaymentMethod()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod(null, $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 502 - Mandatory Fields missing: [/request/paymentmethod]. See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutAmount()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('508', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 508 - Zero, negative or insufficient amount specified.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutCurrency()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withReprocessingCount(1)
                ->withnumberOfPaymentsRemaining(12)
                ->withCustomerNumber('E8953893489')
                ->withOrderPrefix('gym')
                ->withName('Gym Membership')
                ->withDescription('Social Sign-Up')
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('506', $e->responseCode);
            $this->assertStringStartsWith('Unexpected Gateway Response: 506 - The line number 2', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithoutNumberOfPayments()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 502 - Mandatory Fields missing: [/request/numtimes]. See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithNumberOfPaymentsInvalid()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withnumberOfPaymentsRemaining(1000)
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('535', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 535 - Invalid value, numtimes cannot be greater than 999.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardStorageAddSchedule_WithNumberOfPaymentsZero()
    {
        $paymentMethod = $this->newCustomer->addPaymentMethod($this->getPaymentId('Credit'), $this->card);
        $scheduleId = GenerationUtils::generateScheduleId();
        $exceptionCaught = false;

        try {
            $paymentMethod->addSchedule($scheduleId)
                ->withAmount(30.01)
                ->withCurrency('USD')
                ->withFrequency(ScheduleFrequency::QUARTERLY)
                ->withnumberOfPaymentsRemaining(0)
                ->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('535', $e->responseCode);
            $this->assertEquals('Unexpected Gateway Response: 535 - Invalid value, numtimes cannot be greater than 999.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetListOfPaymentSchedules()
    {
        $schedules = RecurringService::search(Schedule::class)
            ->addSearchCriteria(SearchCriteria::PAYMENT_METHOD_KEY, $this->getPaymentId('Credit'))
            ->addSearchCriteria(SearchCriteria::CUSTOMER_ID, $this->getCustomerId())
            ->execute();

        $this->assertNotEmpty($schedules);
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $this->assertNotEmpty($schedule->key);
        }
    }

    public function testGetListOfPaymentSchedules_RandomDetails()
    {
        $exceptionCaught = false;
        $customerId = substr(GenerationUtils::getGuid(), 20);
        try {
            RecurringService::search(Schedule::class)
                ->addSearchCriteria(SearchCriteria::PAYMENT_METHOD_KEY, substr(GenerationUtils::getGuid(), 20))
                ->addSearchCriteria(SearchCriteria::CUSTOMER_ID, $customerId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('520', $e->responseCode);
            $this->assertEquals(sprintf('This Payer Ref [%s] does not exist', $customerId), $e->responseMessage);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetListOfPaymentSchedules_WithoutPayer()
    {
        $exceptionCaught = false;
        try {
            RecurringService::search(Schedule::class)
                ->addSearchCriteria(SearchCriteria::PAYMENT_METHOD_KEY, substr(GenerationUtils::getGuid(), 20))
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertStringContainsString('Mandatory Fields missing', $e->responseMessage);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetListOfPaymentSchedules_WithoutPaymentMethod()
    {
        $exceptionCaught = false;

        try {
            RecurringService::search(Schedule::class)
                ->addSearchCriteria(SearchCriteria::CUSTOMER_ID, substr(GenerationUtils::getGuid(), 20))
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('502', $e->responseCode);
            $this->assertStringContainsString('Mandatory Fields missing', $e->responseMessage);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testDeleteSchedule()
    {
        $schedules = RecurringService::search(Schedule::class)
            ->addSearchCriteria(SearchCriteria::PAYMENT_METHOD_KEY, $this->getPaymentId('Credit'))
            ->addSearchCriteria(SearchCriteria::CUSTOMER_ID, $this->getCustomerId())
            ->execute();

        $this->assertNotEmpty($schedules);
        $schedule = reset($schedules);

        $response = $schedule->delete();
        $this->assertTrue($response instanceof Schedule);

        try {
            RecurringService::get($schedule);
        } catch (GatewayException $e) {
            $this->assertEquals('508', $e->responseCode);
            $this->assertEquals('The Scheduled Payment does not exist.', $e->responseMessage);
        }
    }

    public function testDelete_RandomSchedule()
    {
        $schedule = new Schedule();
        $schedule->key = substr(GenerationUtils::getGuid(), 20);
        $exceptionCaught = false;
        try {
            $schedule->delete();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Failed to delete record, see inner exception for more details', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetPaymentScheduleById()
    {
        $schedule = new Schedule();
        $schedule->key = 'mdq5mgyzzgetmzdlymi4';
        $schedule = RecurringService::get($schedule);

        $this->assertNotNull($schedule);
        $this->assertEquals($schedule->id, $schedule->key);
        $this->assertNotEmpty($schedule->startDate);
    }

    public function testGetPaymentScheduleById_RandomId()
    {
        $schedule = new Schedule();
        $schedule->key = substr(GenerationUtils::getGuid(), 20);
        $exceptionCaught = false;

        try {
            RecurringService::get($schedule);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('508', $e->responseCode);
            $this->assertEquals('The Scheduled Payment does not exist.', $e->responseMessage);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetPaymentScheduleById_NullId()
    {
        $exceptionCaught = false;

        try {
            RecurringService::get(null);
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('key cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}
