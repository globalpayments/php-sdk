<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\GenerationUtils;

class RecurringTest extends TestCase
{

    /** @var $newCustomer */
    public $newCustomer;

    public function getCustomerId()
    {
        return sprintf("%s-Realex", (new \DateTime())->format("Ymd"));
    }

    public function getPaymentId($type)
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

    public function setup()
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
    }

    /* 08. Card Storage Create Payer */
    /* Request Type: payer-new  */

    public function testcardStorageCreatePayer()
    {
        try {
            $response = $this->newCustomer->Create();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->responseCode);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
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
            $paymentMethod = $this->newCustomer
                    ->addPaymentMethod($this->getPaymentId("Credit"), $card)
                    ->create();
            $this->assertNotNull($paymentMethod);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '501' && $exc->responseCode != '520') {
                throw $exc;
            }
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
        $dccDetails = $paymentMethod->getDccRate(DccRateType::SALE, 1001, 'EUR', DccProcessor::FEXCO, $orderId);
        
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
    }

    /* 14. CardStorage UpdatePayer */
    /* Request Type: payer-edit */

    public function testcardStorageUpdatePayer()
    {
        $customer = new Customer();
        $customer->key = $this->getCustomerId();
        $customer->firstName = "Perry";

        $response = $customer->saveChanges();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
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

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
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
        $response = $paymentMethod->Delete();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /* Request Type: receipt-in  */

    public function testcardStorageChargeCardDCC()
    {
        $this->dccSetup();
        $this->testcardStorageCreatePayer();
        $this->testcardStorageStoreCard();
        
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        
        $orderId = GenerationUtils::generateOrderId();
        $dccDetails = $paymentMethod->getDccRate(DccRateType::SALE, 1001, 'EUR', DccProcessor::FEXCO, $orderId);
        
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
        
        $dccValues = new DccRateData();
        $dccValues->orderId = $dccDetails->transactionReference->orderId;
        $dccValues->dccProcessor = DccProcessor::FEXCO;
        $dccValues->dccType = 1;
        $dccValues->dccRateType = DccRateType::SALE;
        $dccValues->currency = $dccDetails->dccResponseResult->cardHolderCurrency;
        $dccValues->dccRate = $dccDetails->dccResponseResult->cardHolderRate;
        $dccValues->amount = $dccDetails->dccResponseResult->cardHolderAmount;
        
        $response = $paymentMethod->charge(1001)
                ->withCurrency("EUR")
                ->withCvn("123")
                ->withDccRateData($dccValues)
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
}
