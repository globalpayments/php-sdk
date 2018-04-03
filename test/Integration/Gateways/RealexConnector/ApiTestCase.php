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
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use PHPUnit\Framework\TestCase;

class ApiTestCase extends TestCase {
    /* 01. Process Payment Authorisation */

    public function testprocessPaymentAuthorisation() {
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
        $card->expYear = 2025;
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

    public function testprocessPaymentRefund() {
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
        $card->expYear = 2025;
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        try {
            // process a refund to the card
            $response = $card->refund(16)
                    ->withCurrency("EUR")
                    ->execute();

            // get the response details to update the DB
            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: add your error handling here
        }
    }

    /* 03. Process Payment OTB */

    public function testprocessPaymentOtb() {
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
        $card->expYear = 2025;
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

    public function testthreeDSecureVerifyEnrolled() {
        // will update later
    }

    /* 05. ThreeD Secure Verify Sig */

    public function testthreeDSecureVerifySig() {
        // will update later
    }

    /* 06.ThreeD Secure Auth */

    public function testthreeDSecureAuth() {
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
        $card->expYear = 2025;
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

    public function testprocessPaymentApplePay() {
        // will update later
    }

    /* 08. Card Storage Create Payer */

    public function testcardStorageCreatePayer() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        $todayDate = date('Ymd');
        $identifierBase = substr(
                sprintf('%s-%%s', GenerationUtils::getGuid()), 0, 10
        );

        // supply the the payer/customer details		
        $customer = new Customer();
        $customer->id = sprintf($identifierBase, $todayDate, 'Person');
        $customer->title = 'Mr.';
        $customer->firstName = 'John';
        $customer->lastName = 'Doe';
        $customer->company = 'Realex Payments';
        $customer->status = 'Active';
        $customer->email = 'text@example.com';
        $customer->address = new Address();
        $customer->address->streetAddress1 = 'Flat 123';
        $customer->address->streetAddress2 = 'House 456';
        $customer->address->city = 'Halifax';
        $customer->address->province = 'TX';
        $customer->address->postalCode = '75024';
        $customer->address->country = 'USA';
        $customer->homePhone = '5551112222';
        $customer->workPhone = '5551112233';
        $customer->fax = '5551112244';
        $customer->mobilePhone = '5551112255';

        try {
            $customer->create();

            // TODO: add a card/payment method to the payer, see next step
            $this->assertNotEquals(null, $customer);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 09. Card Storage Store Card */

    public function testcardStorageStoreCard() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        $todayDate = date('Ymd');
        $identifierBase = substr(
                sprintf('%s-%%s', GenerationUtils::getGuid()), 0, 10
        );

        // supply the the payer/customer details
        $customer = new Customer();
        $customer->key = "e193c21a-ce64-4820-b5b6-8f46715de931";

        // create a new card/payment method reference		
        $paymentMethodRef = sprintf($identifierBase, $todayDate, 'CreditMC');

        // create the card object
        $card = new CreditCardData();
        $card->number = '5473500000000014';
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'James Mason';

        // add the card/payment method to the payer/customer
        $paymentMethod = $customer->addPaymentMethod($paymentMethodRef, $card);

        try {
            // store the card
            $paymentMethod->create();

            // TODO: charge the stored card, see next step
            $this->assertNotEquals(null, $paymentMethod);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 10. Card Storage Charge Card */

    public function testcardStorageChargeCard() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // supply existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // supply existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";

        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        try {
            // charge the stored card/payment method
            $response = $paymentMethod->charge(10)
                    ->withCurrency("USD")
                    ->withCvn("123")
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

    /* 11. CardStorage ThreeDSecure Verify Enrolled */

    public function testcardStorageThreeDSecureVerifyEnrolled() {
        // will update later
    }

    /* 12. CardStorage Dcc Rate Lookup */

    public function testcardStorageDccRateLookup() {
        // will update later
    }

    /* 13. CardStorage DeleteCard */

    public function testcardStorageDeleteCard() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // supply existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // supply existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        try {
            // delete the stored card/payment method
            // WARNING! This can't be undone
            $paymentMethod->Delete();

            $this->assertNotEquals(null, $paymentMethod);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 14. CardStorage UpdatePayer */

    public function testcardStorageUpdatePayer() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // supply the the payer/customer details
        $customer = new Customer();
        $customer->Key = "e193c21a-ce64-4820-b5b6-8f46715de931";
        $customer->title = 'Mr.';
        $customer->firstName = 'John';
        $customer->lastName = 'Doe';
        $customer->company = 'Realex Payments';
        $customer->status = 'Active';
        $customer->email = 'text@example.com';
        $customer->address = new Address();
        $customer->address->streetAddress1 = 'Flat 123';
        $customer->address->streetAddress2 = 'House 456';
        $customer->address->city = 'Halifax';
        $customer->address->province = 'TX';
        $customer->address->postalCode = '75024';
        $customer->address->country = 'USA';
        $customer->homePhone = '4441112222';
        $customer->workPhone = '4441112233';
        $customer->fax = '4441112244';
        $customer->mobilePhone = '4441112255';

        try {
            // update the payer/customer
            $customer->saveChanges();
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 15. CardStorage Continuous Authority */

    public function testcardStorageContinuousAuthority() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // supply existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // supply existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        try {
            // charge the stored card/payment method with continuous authority flags
            $response = $paymentMethod->charge(15)
                    ->withCurrency("EUR")
                    ->withCvn("123")
                    ->withRecurringInfo(RecurringType::VARIABLE, RecurringSequence::FIRST)
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

    /* 16. Card Storage Refund */

    public function testcardStorageRefund() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->refundPassword = 'refund';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        try {
            // refund the stored card/payment method
            $response = $paymentMethod->refund(19.99)
                    ->withCurrency("EUR")
                    ->execute();

            // get the response details to update the DB
            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 17. Card Storage UpdateCard */

    public function testcardStorageUpdateCard() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        // create the card object with new details
        $newCardDetails = new CreditCardData();
        $newCardDetails->number = '5425230000004415';
        $newCardDetails->expMonth = 6;
        $newCardDetails->expYear = 2020;
        $newCardDetails->cardHolderName = 'Philip Marlowe';

        // add the new card details to the payment method object for updating
        $paymentMethod->paymentMethod = $newCardDetails;

        try {
            // update the card details
            $paymentMethod->SaveChanges();

            $this->assertNotEquals(null, $paymentMethod);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 18. Card Storage Verify Card */

    public function testcardStorageVerifyCard() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // existing customer/payer ref
        $customerId = "e193c21a-ce64-4820-b5b6-8f46715de931";
        // existing card/payment method ref
        $paymentId = "10c3e089-fa98-4352-bc4e-4b37f7dcf108";
        // create the payment method object
        $paymentMethod = new RecurringPaymentMethod($customerId, $paymentId);

        try {
            // verify the stored card/payment method is valid and active
            $response = $paymentMethod->verify()
                    ->withCvn("123")
                    ->execute();

            // get the response details to update the DB
            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 19. Transaction Management Delayed Auth */

    public function testtransactionManagementDelayedAuth() {
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
        $card->expYear = 2025;
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

    public function testtransactionManagementSettle() {
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

    public function testtransactionManagementRebate() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->rebatePassword = 'rebate';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a settle request requires the original order id
        $orderId = "QAhN4YFrJEWP6Vc-N68u-w";
        // and the payments reference (pasref) from the authorization response
        $paymentsReference = "15113583374071921";
        // and the auth code transaction response
        $authCode = "12345";

        // create the rebate transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);
        $transaction->authorizationCode = $authCode;

        try {
            // send the settle request, we must specify the amount and currency
            $response = $transaction->refund(99.99)
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

    /* 22. Transaction Management Void */

    public function testtransactionManagementVoid() {
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

    public function testfraudManagementDataSubmission() {
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
        $card->expYear = 2025;
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

        // fraud filter mode configuration coming soon

        try {
            // create the delayed settle authorization
            $response = $card->charge(19.99)
                    ->withCurrency("EUR")
                    ->withAddress($billingAddress, AddressType::BILLING)
                    ->withAddress($shippingAddress, AddressType::SHIPPING)
                    ->withProductId("SID9838383") // prodid
                    ->withClientTransactionId("Car Part HV") // varref
                    ->withCustomerId("E8953893489") // custnum
                    ->withCustomerIpAddress("123.123.123.123")
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
            // TODO: add your error handling here
            // var message = exce.Message; 107 - Fails Fraud Checks
        }
    }

    /* 24. Fraud Management Hold */

    public function testfraudManagementHold() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a hold request requires the original order id
        $orderId = "xd4JTHE0ZEqudur_q1pB1w";
        // and the payments reference (pasref) from the authorization response
        $paymentsReference = "15113583374071921";
        // create the hold transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);

        try {
            // send the hold request, we can choose to specify a reason why we're holding it
            $response = $transaction->hold()
                    ->withReasonCode(ReasonCode::FRAUD)
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 25. Fraud Management Release */

    public function testfraudManagementRelease() {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';

        ServicesContainer::configure($config);

        // a release request requires the original order id
        $orderId = "xd4JTHE0ZEqudur_q1pB1w";
        // and the payments reference (pasref) from the authorization response
        $paymentsReference = "15113583374071921";
        // create the release transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);

        try {
            // send the release request, we can choose to specify a reason why we're releasing it
            $response = $transaction->release()
                    ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                    ->execute();

            $responseCode = $response->responseCode; // 00 == Success
            $message = $response->responseMessage; // [ test system ] AUTHORISED

            $this->assertNotEquals(null, $response);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: Add your error handling here
        }
    }

    /* 26. Dcc Rate Lookup */

    public function testdccRateLookup() {
        // will update later
    }

    /* 27. Dcc Present Choice */

    public function testdccPresentChoice() {
        // will update later
    }

    /* 28. Dcc Auth Data Submission */

    public function testdccAuthDataSubmission() {
        // will update later
    }
    
    /* 29. Google pay */
    
    public function testauthMobileGooglePay()
    {
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
    }
    
    /* 29. Apple pay */
    
    public function testauthMobileApplePay()
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
}
