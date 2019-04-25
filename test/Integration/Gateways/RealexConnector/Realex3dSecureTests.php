<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\CreditService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector\ThreeDSecureAcsClient;
use GlobalPayments\Api\Utils\GenerationUtils;

class Realex3dSecureTests extends TestCase
{
    public function setup() : void
    {
        ServicesContainer::configure($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = '3dsecure';
        $config->sharedSecret = 'secret';
        $config->rebatePassword = 'rebate';
        $config->refundPassword = 'refund';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';
        return $config;
    }

    public function testAcsClient()
    {
        $authClient = new ThreeDSecureAcsClient('https://pit.3dsecure.net/VbVTestSuiteService/pit1/acsService/paReq?summary=MTNmMzI4NzgtNTdmZi00OWEzLWJhZTAtYzFhNzAxMDJkMGNi');
        $this->assertNotNull($authClient->authenticate('eJxlUsFSwjAQvfsVTO82TSm0MNs4FVBwRkUF8ZomK1Rpimkr6NebYBEdc8jsy27evrwNnO3ydesddZkVKnao6zktVKKQmVrGznx2cRo5Z+wEZiuNOHxAUWtkcI1lyZfYymTs+KIjZYRt30tl0H2WPRpFIuQyDULsdTvoMJgm9/jGoOnCTBPXB3KAhk2LFVcVAy7ezic3LAgD2ouANBBy1JMh6zULyDcGxXNkK+S6WnMll5vS7GmxA7JPgChqVekPFgUekAOAWq/Zqqo2ZZ+Q7Xbr/r/visKtX4HYSiBHcdPaRqVh3mWSJcM7Nb7t0O1iGs6n7cXnI025N7hSk1EMxFaA5BUy36MhpX7Y8r1+J+hTI39/Djy3kqwZRl4DYGN7JE3GJn4fgDFfm+EcnnRAgLtNodBUGFd/YiBHwYOx9VZUxrVxdjEb1aPXy5f5k27Tmzo/v75N4ti6vS+wbJlxikb0m84CIJaCNIMkzfxN9OdffAF4VML9'));
    }

    public function testMerchantDataEnumerator()
    {
        $keys = ['Key1', 'Key2', 'Key3'];
        $values = ['Value1', 'Value2', 'Value3'];

        $merchantData = new MerchantDataCollection();
        for ($i=0; $i<3; $i++) {
            $merchantData->add($keys[$i], $values[$i]);
        }

        $this->assertEquals(3, $merchantData->count());

        foreach ($merchantData->getKeys() as $key) {
            $this->assertTrue(in_array($key, $keys));
            $this->assertTrue(in_array($merchantData->get($key), $values));
        }
    }

    public function testMerchantDataWithHiddenValues()
    {
        $card = new CreditCardData();
        $card->number = 4012001037141112;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'James Mason';

        $enrolled = $card->verifyEnrolled(100, 'USD');
        $this->assertNotNull($enrolled);
        if ($enrolled) {
            $merchantData = $card->threeDSecure->getMerchantData();
        
            $this->assertNotNull($merchantData);
            $this->assertEquals(0, $merchantData->count());

            $this->assertNull($merchantData->get('amount'));
            $this->assertNull($merchantData->get('currency'));
            $this->assertNull($merchantData->get('orderId'));

            for ($i=0; $i<3; $i++) {
                $merchantData->add('Key'.$i, 'Value'.$i);

                $this->assertNotNull($merchantData->get('Key'.$i));
                $this->assertEquals('Value'.$i, $merchantData->get('Key'.$i));
            }

            $this->assertEquals(3, $merchantData->count());
        }
    }

    public function testMerchantDataEncryptAndDecrypt()
    {
        $merchantData = new MerchantDataCollection();
        $merchantData->add('customerId', '12345');
        $merchantData->add('invoiceNumber', '54321');

        $encoder = function ($input) {
            $encoded = sprintf('%s.%s', $input, 'secret');
            return base64_encode($encoded);
        };

        $encrypted = $merchantData->toString($encoder);

        $decoder = function ($input) {
            $decoded = explode('.', (string)base64_decode($input));
            $this->assertEquals('secret', $decoded[1]);
            return $decoded[0];
        };

        $decrypted = $merchantData->parse($encrypted, $decoder);

        $this->assertNotNull($decrypted);
        $this->assertNotNull($decrypted->get('customerId'));
        $this->assertEquals('12345', $decrypted->get('customerId'));
        $this->assertNotNull($decrypted->get('invoiceNumber'));
        $this->assertEquals('54321', $decrypted->get('invoiceNumber'));
    }

    public function testMerchantDataMultiKey()
    {
        $this->expectExceptionMessage('Cannot access private property GlobalPayments\Api\Entities\MerchantDataCollection::$collection');

        $mcd = new MerchantDataCollection();
        array_push($mcd->collection, array('amount'=>'10'));
        array_push($mcd->collection, array('amount'=>'10'));
    }

    public function testFullCycleWithMerchantData()
    {
        $card = new CreditCardData();
        $card->number = 4012001037141112;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(1, 'USD');
        if ($enrolled) {
            $secureEcom = $card->threeDSecure;
            if (!empty($secureEcom)) {
                $merchantData = new MerchantDataCollection();
                $merchantData->add('client_txn_id', '123456');

                $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
                $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, (string)$secureEcom->getMerchantData()->toString());
                
                $payerAuthenticationResponse = $authResponse->getAuthResponse();
                $md = MerchantDataCollection::parse($authResponse->getMerchantData());

                if ($card->verifySignature($payerAuthenticationResponse, $md)) {
                    $response = $card->charge(1)
                        ->withCurrency('USD')
                        ->execute();
                    $this->assertNotNull($response);
                    $this->assertEquals('00', $response->responseCode);
                } else {
                    $this->fail('Signature verification failed.');
                }
            } else {
                $this->fail('Secure3Data was null.');
            }
        } else {
            $this->fail('Card not enrolled.');
        }
    }

    public function testFullCycleWithNoMerchantData()
    {
        $card = new CreditCardData();
        $card->number = 4012001037141112;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'James Mason';

        $amount = 100;
        $currency = 'USD';
        $orderId = GenerationUtils::generateOrderId();

        $enrolled = $card->verifyEnrolled($amount, $currency, $orderId);

        if ($enrolled) {
            $secureEcom = $card->threeDSecure;

            if ($secureEcom != null) {
                $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
                $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, (string)$secureEcom->getMerchantData()->toString());
            
                $payerAuthenticationResponse = $authResponse->getAuthResponse();

                if ($card->verifySignature($payerAuthenticationResponse, null, $amount, $currency, $orderId)) {
                    $response = $card->charge($amount)
                        ->withCurrency($currency)
                        ->withOrderId($orderId)
                        ->execute();
                    $this->assertNotNull($response);
                    $this->assertEquals('00', $response->responseCode);
                } else {
                    $this->fail('Signature verification failed.');
                }
            } else {
                $this->fail('Secure3Data was null.');
            }
        } else {
            $this->fail('Card not enrolled.');
        }
    }

    public function testVerifyEnrolledTrue()
    {
        $card = new CreditCardData();
        $card->number = 4012001037141112;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(1, 'USD');
        $this->assertTrue($enrolled);
        $this->assertNotNull($card->threeDSecure);
        $this->assertNotNull($card->threeDSecure->payerAuthenticationRequest);
        $this->assertNotNull($card->threeDSecure->issuerAcsUrl);
        $this->assertNotNull($card->threeDSecure->xid);
    }

    public function testVerifyEnrolledFalse()
    {
        $card = new CreditCardData();
        $card->number = 4012001038443335;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(1, 'USD');
        $this->assertFalse($enrolled);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testVerifySignatureBadOrderId()
    {
        $card = new CreditCardData();
        $card->verifySignature('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', null, 1, 'USD', 'orderId');
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifySignatureNoPaymentResponse()
    {
        $card = new CreditCardData();
        $card->verifySignature(null);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifySignatureNoAmount()
    {
        $card = new CreditCardData();
        $card->verifySignature('paymentResponse', null, null, 'USD', 'orderId');
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifySignatureNoCurrency()
    {
        $card = new CreditCardData();
        $card->verifySignature('paymentResponse', null, 10, null, 'orderId');
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testVerifySignatureNoOrderId()
    {
        $card = new CreditCardData();
        $card->verifySignature('paymentResponse', null, 10, 'USD', null);
    }

    public function testAuthorize3dSecure()
    {
        $secureEcom = new ThreeDSecure();
        $secureEcom->cavv = 'AAACBllleHchZTBWIGV4AAAAAAA=';
        $secureEcom->xid = 'crqAeMwkEL9r4POdxpByWJ1/wYg=';
        $secureEcom->eci = '5';

        $card = new CreditCardData();
        $card->number = 4012001037141112;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'Philip Marlowe';
        $card->threeDSecure = $secureEcom;

        $response = $card->charge(10)
            ->withCurrency('EUR')
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCardHolderNotEnrolled()
    {
        $card = new CreditCardData();
        $card->number = 4012001038443335;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertFalse($enrolled);
        $this->assertNotNull($card->threeDSecure);
        $this->assertEquals('6', $card->threeDSecure->eci);

        // .net test does not have amount or currency but validation would not allow this
        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testUnableToVerifyEnrollment()
    {
        $card = new CreditCardData();
        $card->number = 4012001038488884;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertFalse($enrolled);
        $this->assertNotNull($card->threeDSecure);
        $this->assertEquals('7', $card->threeDSecure->eci);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testInvalidResponseFromEnrollmentServer()
    {
        $card = new CreditCardData();
        $card->number = 4012001036298889;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $card->verifyEnrolled(10, 'USD');
    }

    public function testCardHolderIsEnrolledACSAuthFailed()
    {
        $card = new CreditCardData();
        $card->number = 4012001036853337;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertTrue($enrolled);

        $secureEcom = $card->threeDSecure;
        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, $secureEcom->getMerchantData()->toString());
    
        // $payerAuthenticationResponse = $authResponse->parse();
        $payerAuthenticationResponse = $authResponse->getAuthResponse();
        $md = MerchantDataCollection::parse($authResponse->getMerchantData());

        $verified = $card->verifySignature($payerAuthenticationResponse, $md);
        $this->assertFalse($verified);
        $this->assertNotNull($card->threeDSecure);
        $this->assertEquals(7, $card->threeDSecure->eci);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCardHolderIsEnrolledACSAcknowledged()
    {
        $card = new CreditCardData();
        $card->number = 4012001037167778;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertTrue($enrolled);

        $secureEcom = $card->threeDSecure;
        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, $secureEcom->getMerchantData()->toString());
    
        // $payerAuthenticationResponse = $authResponse->parse();
        $payerAuthenticationResponse = $authResponse->getAuthResponse();
        $md = MerchantDataCollection::parse($authResponse->getMerchantData());

        $verified = $card->verifySignature($payerAuthenticationResponse, $md);
        $this->assertTrue($verified);
        $this->assertEquals('A', $card->threeDSecure->status);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCardHolderIsEnrolledACSFailed()
    {
        $card = new CreditCardData();
        $card->number = 4012001037461114;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertTrue($enrolled);

        $secureEcom = $card->threeDSecure;
        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, $secureEcom->getMerchantData()->toString());
    
        // $payerAuthenticationResponse = $authResponse->parse();
        $payerAuthenticationResponse = $authResponse->getAuthResponse();
        $md = MerchantDataCollection::parse($authResponse->getMerchantData());

        $verified = $card->verifySignature($payerAuthenticationResponse, $md);
        $this->assertFalse($verified);
        $this->assertEquals('N', $card->threeDSecure->status);
        $this->assertEquals(7, $card->threeDSecure->eci);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCardHolderIsEnrolledACSUnavailable()
    {
        $card = new CreditCardData();
        $card->number = 4012001037484447;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertTrue($enrolled);

        $secureEcom = $card->threeDSecure;
        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, $secureEcom->getMerchantData()->toString());
    
        $payerAuthenticationResponse = $authResponse->getAuthResponse();
        $md = MerchantDataCollection::parse($authResponse->getMerchantData());

        $verified = $card->verifySignature($payerAuthenticationResponse, $md);
        $this->assertFalse($verified);
        $this->assertEquals('U', $card->threeDSecure->status);
        $this->assertEquals(7, $card->threeDSecure->eci);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCardHolderIsEnrolledACSInvalid()
    {
        $card = new CreditCardData();
        $card->number = 4012001037490006;
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cardHolderName = 'John Smith';

        $enrolled = $card->verifyEnrolled(10, 'USD');
        $this->assertTrue($enrolled);

        $secureEcom = $card->threeDSecure;
        $authClient = new ThreeDSecureAcsClient($secureEcom->issuerAcsUrl);
        $authResponse = $authClient->authenticate($secureEcom->payerAuthenticationRequest, $secureEcom->getMerchantData()->toString());
    
        $payerAuthenticationResponse = $authResponse->getAuthResponse();
        $md = MerchantDataCollection::parse($authResponse->getMerchantData());

        $card->verifySignature($payerAuthenticationResponse, $md);
    }
}
