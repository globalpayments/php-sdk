<?php

namespace GlobalPayments\Api\Test\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\ReasonCode;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\DecisionManager;
use GlobalPayments\Api\Entities\Enums\Risk;

class ApiTestCase extends TestCase
{
    /* 01. Process Payment Authorisation */

    public function testprocessPaymentAuthorisation()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        try {
            // process an auto-settle authorization
            $response = $card->charge(15)
                    ->withCurrency("EUR")
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED
            // get the details to save to the DB for future Transaction Management requests
            $orderId = $response->orderId;
            $authCode = $response->authorizationCode;
            $paymentsReference = $response->transactionId;
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: add your error handling here
        }
    }

    /* 02. Process Payment Refund */

    public function testprocessPaymentRefund()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->refundPassword = 'refund';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // process a refund to the card
        $response = $card->refund(16)
                ->withCurrency("EUR")
                ->execute();

        // get the response details to update the DB
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 03. Process Payment OTB */

    public function testprocessPaymentOtb()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        try {
            // check that a card is valid and active without charging an amount
            $response = $card->verify()
                    ->execute();

            // get the response details to update the DB
            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED
            // TODO: save the card to Card Stroage

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 04. ThreeD Secure Verify Enrolled */

    public function testthreeDSecureVerifyEnrolled()
    {
        // will update later
    }

    /* 05. ThreeD Secure Verify Sig */

    public function testthreeDSecureVerifySig()
    {
        // will update later
    }

    /* 06.ThreeD Secure Auth */

    public function testthreeDSecureAuth()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // supply the details from the 3D Secure verify-signature response
        $threeDSecureInfo = new EcommerceInfo();
        $threeDSecureInfo->cavv = "AAACBllleHchZTBWIGV4AAAAAAA=";
        $threeDSecureInfo->xid = "crqAeMwkEL9r4POdxpByWJ1/wYg=";
        $threeDSecureInfo->eci = "5";

        try {
            // create the authorization with 3D Secure information
            $response = $card->charge(15)
                    ->withEcommerceInfo($threeDSecureInfo)
                    ->withCurrency("EUR")
                    ->execute();

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $response->responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 07. Process Payment Apple Pay */

    public function testprocessPaymentApplePay()
    {
        // will update later
    }

    /* 19. Transaction Management Delayed Auth */

    public function testtransactionManagementDelayedAuth()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        try {
            // create the delayed settle authorization
            $response = $card->authorize(19.99)
                    ->withCurrency("EUR")
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED
            // get the reponse details to save to the DB for future transaction management requests
            $orderId = $response->orderId;
            $authCode = $response->authorizationCode;
            $paymentsReference = $response->transactionId; // pasref

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 20. Transaction Management Settle */

    public function testtransactionManagementSettle()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a settle request requires the original order id
        $orderId = "QAhN4YFrJEWP6Vc-N68u-w";
        // and the payments reference (pasref) from the authorization response
        $paymentsReference = "15113583374071921";
        // create the settle transaction object
        $settle = Transaction::fromId($paymentsReference, $orderId);

        try {
            // send the settle request, we must specify the amount and currency
            $response = $settle->capture(1)
                    ->withCurrency("EUR")
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 21. Transaction Management Rebate */

    public function testTransactionManagementRebate()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->rebatePassword = 'rebate';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);
        
        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';
        
        $response = $card->charge(19.99)
                ->withCurrency("EUR")
                ->execute();

        $this->assertNotNull($response);

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref
        
        // create the rebate transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);
        $transaction->authorizationCode = $authCode;

        // send the settle request, we must specify the amount and currency
        $response = $transaction->refund(19.99)
                ->withCurrency("EUR")
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 22. Transaction Management Void */

    public function testtransactionManagementVoid()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a void request requires the original order id
        $orderId = "xd4JTHE0ZEqudur_q1pB1w";
        // and the payments reference (pasref) from the transaction response
        $paymentsReference = "15113573969816936";
        // create the void transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);

        try {
            // send the void request
            $response = $transaction->void()
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 23. Fraud Management Data Submission */

    public function testfraudManagementDataSubmission()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // supply the customer's billing country and post code for avs checks
        $billingAddress = new Address();
        $billingAddress->postalCode = "50001|Flat 123";
        $billingAddress->country = "US";

        // supply the customer's shipping country and post code
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "654|123";
        $shippingAddress->country = "GB";

        // create the delayed settle authorization
        $response = $card->charge(10)
                ->withCurrency("EUR")
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withAddress($shippingAddress, AddressType::SHIPPING)
                ->withProductId("SID9838383") // prodid
                ->withClientTransactionId("Car Part HV") // varref
                ->withCustomerId("E8953893489") // custnum
                ->withCustomerIpAddress("123.123.123.123")
                ->withFraudFilter(FraudFilterMode::PASSIVE)
                ->execute();
        
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotNull($response);
        $this->assertEquals("00", $responseCode);
        $this->assertNotNull($response->fraudFilterResponse);
    }

    /* 24. Fraud Management Hold */

    public function testfraudManagementHold()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a hold request requires the original order id
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        $response = $card->authorize(19.99)
                ->withCurrency("EUR")
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);

        // create the hold transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);

        // send the hold request, we can choose to specify a reason why we're holding it
        $response = $transaction->hold()
                ->withReasonCode(ReasonCode::FRAUD)
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 25. Fraud Management Release */

    public function testfraudManagementRelease()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a hold request requires the original order id
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        $response = $card->authorize(19.99)
                ->withCurrency("EUR")
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);

        // create the hold transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);

        // send the hold request, we can choose to specify a reason why we're holding it
        $response = $transaction->hold()
                ->withReasonCode(ReasonCode::FRAUD)
                ->execute();
        
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);

        // send the release request, we can choose to specify a reason why we're releasing it
        $response = $transaction->release()
                ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }

    /* 26. Dcc Rate Lookup */

    public function testdccRateLookup()
    {
        // will update later
    }

    /* 27. Dcc Present Choice */

    public function testdccPresentChoice()
    {
        // will update later
    }

    /* 28. Dcc Auth Data Submission */

    public function testdccAuthDataSubmission()
    {
        // will update later
    }
    
    /* 29. Google pay */
    
    public function testauthMobileGooglePay()
    {
        try {
            $config = new ServicesConfig();
            $config->merchantId = 'heartlandgpsandbox';
            $config->accountId = 'apitest';
            $config->sharedSecret = 'secret';
            $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

            ServicesContainer::configure($config);

            // create the card object
            $card = new CreditCardData();
            $card->token = '{"signature":"MEUCIQDapDDJyf9lH3ztEWksgAjNe...AXjW+ZM+Ut2BWoTExppDDPc1a9Z7U\u003d","protocolVersion":"ECv1","signedMessage":"{\"encryptedMessage\":\"VkqwkFuMdXp...TZQxVMnkTeJjwyc4\\u003d\",\"ephemeralPublicKey\":\"BMglUoKZWxgB...YCiBNkLaMTD9G4sec\\u003d\",\"tag\":\"4VYypqW2Q5FN7UP87QNDGsLgc48vAe5+AcjR+BxQ2Zo\\u003d\"}"}';
            $card->mobileType = EncyptedMobileType::GOOGLE_PAY;

            // process an auto-settle authorization
            $response = $card->charge(15)
                    ->withCurrency("EUR")
                    ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED
            // get the details to save to the DB for future Transaction Management requests
            $orderId = $response->orderId;
            $authCode = $response->authorizationCode;
            $paymentsReference = $response->transactionId;
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '509') {
                throw $exc;
            }
        }
    }
    
    /* 30. Apple pay */
    
    public function testauthMobileApplePay()
    {
        try {
            $config = new ServicesConfig();
            $config->merchantId = 'heartlandgpsandbox';
            $config->accountId = 'apitest';
            $config->sharedSecret = 'secret';
            $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

            ServicesContainer::configure($config);

            // create the card object
            $card = new CreditCardData();
            $card->token = '{"version":"EC_v1","data":"dvMNzlcy6WNB","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEWdNhNAHy9kO2Kol33kIh7k6wh6E","transactionId":"fd88874954acdb299c285f95a3202ad1f330d3fd4ebc22a864398684198644c3","publicKeyHash":"h7WnNVz2gmpTSkHqETOWsskFPLSj31e3sPTS2cBxgrk"}}';
            $card->mobileType = EncyptedMobileType::APPLE_PAY;

            // process an auto-settle authorization
            $response = $card->charge()
                    ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED
            // get the details to save to the DB for future Transaction Management requests
            $orderId = $response->orderId;
            $authCode = $response->authorizationCode;
            $paymentsReference = $response->transactionId;
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (GatewayException $exc) {
            if ($exc->responseCode != '509' && $exc->responseCode != '515') {
                throw $exc;
            }
        }
    }
    
    /* 31. Mobile payment without Token value */

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  token cannot be null for this transaction type
     */
    public function testauthMobileWithoutToken()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'apitest';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $response = $card->charge(15)
            ->withCurrency("EUR")
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();
    }
    
    /* 32. Mobile payment without Mobile Type */

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  mobileType cannot be null for this transaction type
     */
    public function testauthMobileWithoutType()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'apitest';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->token = '{"version":"EC_v1","data":"dvMNzlcy6WNB","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEWdNhNAHy9kO2Kol33kIh7k6wh6E","transactionId":"fd88874954acdb299c285f95a3202ad1f330d3fd4ebc22a864398684198644c3","publicKeyHash":"h7WnNVz2gmpTSkHqETOWsskFPLSj31e3sPTS2cBxgrk"}}';

        $response = $card->charge(15)
            ->withCurrency("EUR")
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();
    }
    
    /* 33. Google payment without amount */

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  Amount and Currency cannot be null for google payment
     */
    public function testauthMobileWithoutAmount()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'apitest';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->token = '{"version":"EC_v1","data":"dvMNzlcy6WNB","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEWdNhNAHy9kO2Kol33kIh7k6wh6E","transactionId":"fd88874954acdb299c285f95a3202ad1f330d3fd4ebc22a864398684198644c3","publicKeyHash":"h7WnNVz2gmpTSkHqETOWsskFPLSj31e3sPTS2cBxgrk"}}';
        $card->mobileType = EncyptedMobileType::GOOGLE_PAY;
        
        $response = $card->charge()
            ->withCurrency("EUR")
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();
    }
    
    /* 34. Google payment without Currency */

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  Amount and Currency cannot be null for google payment
     */
    public function testauthMobileWithoutCurrency()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'apitest';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->token = '{"version":"EC_v1","data":"dvMNzlcy6WNB","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEWdNhNAHy9kO2Kol33kIh7k6wh6E","transactionId":"fd88874954acdb299c285f95a3202ad1f330d3fd4ebc22a864398684198644c3","publicKeyHash":"h7WnNVz2gmpTSkHqETOWsskFPLSj31e3sPTS2cBxgrk"}}';
        $card->mobileType = EncyptedMobileType::GOOGLE_PAY;
        
        $response = $card->charge(12)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();
    }
    
    public function testfraudManagementAVSMatch()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // supply the customer's billing country and post code for avs checks
        $billingAddress = new Address();
        $billingAddress->postalCode = "50001|Flat 123";
        $billingAddress->country = "US";
        
        // create the delayed settle authorization
        $response = $card->charge(10)
                ->withCurrency("EUR")
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withVerifyAddress(true)
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotNull($response);
        $this->assertEquals("00", $responseCode);
        $this->assertEquals("M", $response->avsResponseCode);
        $this->assertEquals("M", $response->avsAddressResponse);
    }
    
    public function testfraudManagementOffMode()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // supply the customer's billing country and post code for avs checks
        $billingAddress = new Address();
        $billingAddress->postalCode = "50001|Flat 123";
        $billingAddress->country = "US";

        // supply the customer's shipping country and post code
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "654|123";
        $shippingAddress->country = "GB";

        // create the delayed settle authorization
        $response = $card->charge(10)
                ->withCurrency("EUR")
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withAddress($shippingAddress, AddressType::SHIPPING)
                ->withProductId("SID9838383") // prodid
                ->withClientTransactionId("Car Part HV") // varref
                ->withCustomerId("E8953893489") // custnum
                ->withCustomerIpAddress("123.123.123.123")
                ->withFraudFilter(FraudFilterMode::OFF)
                ->execute();
        
        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref

        $this->assertNotNull($response);
        $this->assertEquals("00", $responseCode);
        $this->assertNull($response->fraudFilterResponse);
    }

    /* 35. Fraud Management Decision Manager */

    public function testfraudManagementDecisionManager()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // supply the customer's billing data for avs checks
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123";
        $billingAddress->streetAddress2 = "House 456";
        $billingAddress->streetAddress3 = "Cul-De-Sac";
        $billingAddress->city = "Halifax";
        $billingAddress->province = "West Yorkshire";
        $billingAddress->state = "Yorkshire and the Humber";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // supply the customer's shipping data
        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = "House 456";
        $shippingAddress->streetAddress2 = "987 The Street";
        $shippingAddress->streetAddress3 = "Basement Flat";
        $shippingAddress->city = "Chicago";
        $shippingAddress->province = "Illinois";
        $shippingAddress->state = "Mid West";
        $shippingAddress->postalCode = "50001";
        $shippingAddress->country = "US";

        // supply the customer's data
        $customer = new Customer();
        $customer->id = "e193c21a-ce64-4820-b5b6-8f46715de931";
        $customer->firstName = "James";
        $customer->lastName = "Mason";
        $customer->dateOfBirth = "01011980";
        $customer->customerPassword = "VerySecurePassword";
        $customer->email = "text@example.com";
        $customer->domainName = "example.com";
        $customer->homePhone = "+35312345678";
        $customer->deviceFingerPrint = "devicefingerprint";

        // supply the decisionManager data
        $decisionManager = new DecisionManager();
        $decisionManager->billToHostName = "example.com";
        $decisionManager->billToHttpBrowserCookiesAccepted = true;
        $decisionManager->billToHttpBrowserEmail = "jamesmason@example.com";
        $decisionManager->billToHttpBrowserType = "Mozilla";
        $decisionManager->billToIpNetworkAddress = "123.123.123.123";
        $decisionManager->businessRulessCoreThresHold = "40";
        $decisionManager->billToPersonalId = "741258963";
        $decisionManager->decisionManagerProfile = "DemoProfile";
        $decisionManager->invoiceHeaderTenderType = "consumer";
        $decisionManager->itemHostHedge = Risk::HIGH;
        $decisionManager->itemNonsensicalHedge = Risk::HIGH;
        $decisionManager->itemObscenitiesHedge = Risk::HIGH;
        $decisionManager->itemPhoneHedge = Risk::HIGH;
        $decisionManager->itemTimeHedge = Risk::HIGH;
        $decisionManager->itemVelocityHedge = Risk::HIGH;
        $decisionManager->invoiceHeaderIsGift = true;
        $decisionManager->invoiceHeaderReturnsAccepted = true;

        $products = [];
        $products[] = array(
                    'product_id' => 'SKU251584',
                    'productname' => 'Magazine Subscription',
                    'quantity' => '12',
                    'unitprice' => '1200',
                    'gift' => 'true',
                    'type' => 'subscription',
                    'risk' => 'Low'
                );
        $products[] = array(
                    'product_id' => 'SKU8884784',
                    'productname' => 'Charger',
                    'quantity' => '10',
                    'unitprice' => '1200',
                    'gift' => 'false',
                    'type' => 'subscription',
                    'risk' => 'High'
                );

        $custom = [];
        $custom[] = array(
                    'field01' => 'fieldValue01',
                    'field02' => 'fieldValue02',
                    'field03' => 'fieldValue03',
                    'field04' => 'fieldValue04'
                );

        $response = $card->charge(199.99)
                ->withCurrency("EUR")
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withAddress($shippingAddress, AddressType::SHIPPING)
                ->withDecisionManager($decisionManager)
                ->withCustomerData($customer)
                ->withProductData($products)
                ->withCustomData($custom)
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED

        $this->assertNotNull($response);
        $this->assertEquals("00", $responseCode);
    }
    
    public function testAuthorisationWithoutAccountId()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // process an auto-settle authorization
        $response = $card->charge(15)
                ->withCurrency("EUR")
                ->execute();

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testRefundWithoutAccountId()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->sharedSecret = 'secret';
        $config->refundPassword = 'refund';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // process a refund to the card
        $response = $card->refund(16)
                ->withCurrency("EUR")
                ->execute();

        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $response->responseCode);
    }
}
