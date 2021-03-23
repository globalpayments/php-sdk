<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector\Certifications;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\ReasonCode;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class RealexSdkCertification extends TestCase
{
    public function tearDown()
    {
        usleep(1500000);
    }

    protected function getBaseConfig()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "api";
        $config->sharedSecret = "secret";
        $config->refundPassword = "refund";
        $config->rebatePassword = "rebate";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->timeout = 20000;
        return $config;
    }

    public function getBaseCardData()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;
        $card->cardHolderName = "James Mason";
        return $card;
    }

    public function testAuth006a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-006a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-006b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-006c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-006d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-006e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006f()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-006f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006g()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-006g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006h()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-006h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006i()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-006i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006j()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-006j")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth006k()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-006k")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth007a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-007a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth007b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-007b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth007c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-007c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth007d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-007d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth007e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-007e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth008a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-008a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth008b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-008b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth008c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-008c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth008d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-008d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth008e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-008e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-009a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "E";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-009b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOMMERCE";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-009c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth009d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-009d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth010a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-010a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth010b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-010b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth010c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-010c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth010d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-010d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth010e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-010e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth011a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-011a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth011b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-011b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth011c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-011c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuth011d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-011d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth012a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-012a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth012b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EURO")
            ->withDescription("JAVA-Auth-012b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth012c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("�UR")
            ->withDescription("JAVA-Auth-012c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testAuth012d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withDescription("JAVA-Auth-012d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth013a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-013a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth013b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "424242000000000000000";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-013b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth013b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "42424242424";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-013b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth013c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4263970000005262#";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-013c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth014a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-014a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth014b()
    {
        $this->markTestSkipped('Exception not thrown');
        
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-014b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth014c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-014c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth014d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "James~Mason";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-014d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth015a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-015a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth015b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-015b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth015c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 20;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-015c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth015d()
    {
        $this->markTestSkipped('Exception not thrown');
        
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-015d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth016a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-016a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth016b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-016b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth016c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-016c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth017a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-017a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth018a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-018a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth019a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-019a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth019b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-019b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth019b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-019b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth019c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12345";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-019c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth019d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-019d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth020a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-020a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth020a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::ILLEGIBLE;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-020a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth020a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_ON_CARD;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-020a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth020a4()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-020a4")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth020b()
    {
        $this->markTestSkipped('Exception not thrown');
        
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = 5;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-020b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth020c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = 0;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-020c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth021a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-021a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth021a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->authorize(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-021a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth021a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->authorize(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-021a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth021b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->authorize(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-021b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth021c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->authorize(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-021c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth022a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-022a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth022b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-022b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth022c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-022c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth022d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-022d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth022e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-022e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth023a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-023a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth023a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-023a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth023b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-023b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth023c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-023c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth024a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-024a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth024a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-024a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth024a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-024a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth024b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-024b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth024c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-024c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth025()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-025")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth026a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-026a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth026a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth026b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth026c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIep3uviSnW9XEB3a4wpIW9XEB3a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth026c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-026c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth027a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerId("123456")
            ->withDescription("JAVA-Auth-027a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth028a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("123456")
            ->withDescription("JAVA-Auth-028a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth028b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-028b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth028c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Auth-028c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth028d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("123456~")
            ->withDescription("JAVA-Auth-028d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth029a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withProductId("123456")
            ->withDescription("JAVA-Auth-029a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth029b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Auth-029b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth029c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withProductId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Auth-029c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth029d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withProductId("123456~")
            ->withDescription("JAVA-Auth-029d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth030a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withClientTransactionId("123456")
            ->withDescription("JAVA-Auth-030a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth030b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-030b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth030c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withClientTransactionId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Auth-030c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth030d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withClientTransactionId("123456~")
            ->withDescription("JAVA-Auth-030d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth031a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerIpAddress("123.123.123.123")
            ->withDescription("JAVA-Auth-031a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth031b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-031b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth031c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerIpAddress("1234.123.123.123")
            ->withDescription("JAVA-Auth-031c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth031c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerIpAddress("123~.123.123.123")
            ->withDescription("JAVA-Auth-031c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth032a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "United Kingdom";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "Z76 PO9";
        $shippingAddress->country = "France";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-032a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth033a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "774|10";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "769|52";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-033a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth033b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "774|10";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Auth-033b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth033b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "769|52";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-033b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth033c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Auth-033c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth033c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-033c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth034a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-034a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth034b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "GB";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-034b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth034b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Auth-034b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth034c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Auth-034c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAuth034c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-034c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth035a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Auth-035a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth035b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Auth-035b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testAuth055a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "774|10";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "769|52";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withCustomerId("12345")
            ->withProductId("654321")
            ->withClientTransactionId("987654")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Auth-055a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002c1()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002c2()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation002d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "V002625938386848";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation002f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = " 4002 6259 3838 6848";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation002g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation002h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-002h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation003a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation003b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation003c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 20;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation003d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation003e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 11;
        $card->expYear = 5; // magic number?

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation003f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation003g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = 20; // magic number?

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation003h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation003i()
    {
        $this->markTestSkipped('Exception not thrown');
        
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-003i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation004b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation004c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12345";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation004e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = 0;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = CvnPresenceIndicator::ILLEGIBLE;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_ON_CARD;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation004i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-004i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation005b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation005h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-005h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-006a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-006b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 11;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-006c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 11;
        $card->expYear = 5; //magic number?

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-006d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-006e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5425230000004415";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-007a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5425230000004415";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-007b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5425230000004415";
        $card->expMonth = 11;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-007d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5425230000004415";
        $card->expMonth = 11;
        $card->expYear = 5; // magic number?

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-007e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation007f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5425230000004415";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-007f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testValidation008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-008b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 11;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-008c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 11;
        $card->expYear = 5; // magic number?
        $card->cvn = "1234";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-008d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "30384800000000";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-009b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "30450100000000";
        $card->expMonth = 11;
        $card->expYear = TestCards::expiredCardExpYear();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-009c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testValidation009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "779|102";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "658|325";
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->number = "30450100000000";
        $card->expMonth = 11;
        $card->expYear = 5; // magic number?

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Validation-009d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat #123 House No. 456";
        $billingAddress->postalCode = "E77 #4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "2";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "# Flat #123 House No. #456";
        $billingAddress->postalCode = "# E77 @~4 Q # J";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "3";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "4";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Lorem ipsum dolor sit 1amet; consectetur adipiscing elit. Aenean ali2quam tellus in elit hendrerit; non 3porttE77 4QJitor lorem venenatis. Pellentesque dictum eu nunc ac fringilla. In vitae quam eu odio sollicitudin rhoncus. Praesent ullamcorper eros vitae consequat tempus. In gravida viverra iaculis. Morbi dignissim orci et ipsum accumsan";
        $billingAddress->postalCode = "Lorem ipsum dolo1r sit amet; consectetur adipiscing elit. Aenean aliquam tellus in elit hendrerit; non porttE77 4QJitor lorem venenatis. Pellentesque dictum eu2 nunc ac fringilla. In vitae quam eu 3odio sollicitudin rhoncus. Praesent ullamcorper eros vitae consequat tempus. In gravida viverra iaculis. Morbi dignissim orci et ipsum accumsan";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "5";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "ABCDEFGHIJ";
        $billingAddress->postalCode = "ABCDEFGHIJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "6";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS001g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Lorem ipsum dolor sit amet; consectetur adipiscing elit. Aenean aliquam tellus in elit hendrerit; non porttE77 4QJitor lorem venenatis. Pellentesque dictum eu nunc ac fringilla. In vitae quam eu odio sollicitudin rhoncus. Praesent ullamcorper eros vitae consequat tempus. In gravida viverra iaculis. Morbi dignissim orci et ipsum accumsan";
        $billingAddress->postalCode = "Lorem ipsum dolor sit amet; consectetur adipiscing elit. Aenean aliquam tellus in elit hendrerit; non porttE77 4QJitor lorem venenatis. Pellentesque dictum eu nunc ac fringilla. In vitae quam eu odio sollicitudin rhoncus. Praesent ullamcorper eros vitae consequat tempus. In gravida viverra iaculis. Morbi dignissim orci et ipsum accumsan";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "7";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-001g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "8";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "9";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "10";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();

        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "11";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Flat 123 House 456";
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "13";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "14";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testAVS003h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "15";

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("100")
            ->withProductId("999")
            ->withClientTransactionId("test")
            ->withCustomerIpAddress("123.123.123.123")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-AVS-003f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettleSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle006k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testSettle009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOm";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testSettle009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECO#";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle010e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle011b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle011c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle011d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle012b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1.005)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1.005)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSettle012c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSettle012d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle012e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1000)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1000)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testSettle012f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testSettle013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EURO")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EURO")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testSettle013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EU#")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EU#")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle013d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle016a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle016b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle016c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-SettleAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle016d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle###")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testSettle017a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Settle")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testSettle017b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->authorize(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->capture(1)
            ->withCurrency("EUR")
            ->withDescription("SDK-JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoidSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid006k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testVoid009e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testVoid009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOm";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testVoid009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECO#";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid010e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid011b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid011c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid011d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid012b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid012c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid012d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("akshdfgakjdshfgjdshgfkjdsahgfjshagdfjshdagfkjdshgfjshdgfjdshgfkjhdsagfjdsgfdskjgfdsjkhgfdsjhgfkdsjgfkjdshgfkjdsahgfjdskhgfjhdsgfjkhsdgfjkhsdgfjhsdgfjhsdgfkjhgsadjfhgsakjdhgfsajdhgfkjsadgfjhsadgfjkhdsgafjhdsgfjhdsgfjhdsgfkjhdgsafjkhgsfjhsdagfkjsgdafjhsgdfjhgdskjfgdsjfhgjdskhgfjhdsgfjhdsgfkjhgdsfkjhgsdkjfgsdkjhgfkjsahgdfkjgdsajfhgdsjkgfjdshgfjkdsagfjkhdsgfjsdhgfjkdshgfkjhgdsfkjhgdskjfgdskjgfkjdsahgfjhgdsakjfgdsafjhgdsjkhgfkjdshgfakjadshgfjhdsagfjhgdsfjhgsdakjfgdsakjhgfjsdhgfjhdsgfjhdsgfkjgdsajkfhgjdshgfjdsahgfjkhdsagfjhdsgfjkgdsfjhdsgfjhgdsjfhgdsjhfgjdshgfkjdsgfkjsadgfjkgdsfkjhgdsajfkhgdsjkgfkjdsagfkjgdsakjfhgdsfjkhgdsafkjgsadkjgfdkjsahgfkjsagfkjdshgfkjshdgfjgdsfkjgsadkjhgfdsjhgfkjdsagfjhdsgfjhgdsakjfgdsakjhgfjsdahgfjkgdsfjhgdsajkhfgjhdsagfkjhsgdakjf")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("SDK#####")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testVoid015b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->void()
            ->withDescription("JAVA-Void")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebateSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOm";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECO#";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate010e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate011b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EURO")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EURO")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate011c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EU##")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EU##")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRebate011d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate012b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1.005)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1.005)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate012c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate012d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate012e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100000)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(100000)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate012f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate013d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate016a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate016b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate016c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("akshdfgakjdshfgjdshgfkjdsahgfjshagdfjshdagfkjdshgfjshdgfjdshgfkjhdsagfjdsgfdskjgfdsjkhgfdsjhgfkdsjgfkjdshgfkjdsahgfjdskhgfjhdsgfjkhsdgfjkhsdgfjhsdgfjhsdgfkjhgsadjfhgsakjdhgfsajdhgfkjsadgfjhsadgfjkhdsgafjhdsgfjhdsgfjhdsgfkjhdgsafjkhgsfjhsdagfkjsgdafjhsgdfjhgdskjfgdsjfhgjdskhgfjhdsgfjhdsgfkjhgdsfkjhgsdkjfgsdkjhgfkjsahgdfkjgdsajfhgdsjkgfjdshgfjkdsagfjkhdsgfjsdhgfjkdshgfkjhgdsfkjhgdskjfgdskjgfkjdsahgfjhgdsakjfgdsafjhgdsjkhgfkjdshgfakjadshgfjhdsagfjhgdsfjhgsdakjfgdsakjhgfjsdhgfjhdsgfjhdsgfkjgdsajkfhgjdshgfjdsahgfjkhdsagfjhdsgfjkgdsfjhdsgfjhgdsjfhgdsjhfgjdshgfkjdsgfkjsadgfjkgdsfkjhgdsajfkhgdsjkgfkjdsagfkjgdsakjfhgdsfjkhgdsafkjgsadkjgfdkjsahgfkjsagfkjdshgfkjshdgfjgdsfkjgsadkjhgfdsjhgfkjdsagfjhdsgfjhgdsakjfgdsakjhgfjsdahgfjkgdsfjhgdsajkhfgjhdsagfkjhsgdakjf")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate016d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("SDK#####")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRebate017a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRebate017b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(1)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Rebate")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTBSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->charge(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB006l()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB010e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB012b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB012c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB012d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB012e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermeloooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB013d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 18;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = null;
        $card->expYear = null;
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB015b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB015c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB016a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB017a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB017b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB017c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1.23457E+18";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB017d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB017f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "7";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB017g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "7";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testOTB018a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testOTB018b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-OTB")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCreditSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit010e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit012b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit012c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testCredit013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = 1813; // magic number?
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 18;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = null;
        $card->expYear = null;
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit015b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit015c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit016a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit017a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit017b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit017c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123456789";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit017d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit017f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4242424242424240";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "7";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit017g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12#";
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit018a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit018b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit018c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit018d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit019a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit019b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit019c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit020a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit020b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit021a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit021b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1.005)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testCredit021c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testCredit021d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit021e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(100000)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testCredit021f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testCredit022a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit022b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EURO")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testCredit022c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withCurrency("EU#")
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testCredit022d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "Peter Watermelon";

        // request
        $response = $card->refund(1)
            ->withDescription("JAVA-Credit")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHoldSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold006l()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold008a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold008b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("SDK-JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold009e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("SDK-JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold010a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold010b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testHold010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold011b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(null)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold011c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold011d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(null)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testHold012b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("SDK-JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testHold013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testHold013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("SDK-JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testHold013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("SDK-JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testReleaseSample()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease006l()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease007a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease007b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease007c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // hold it first
        $holdResponse = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OUT_OF_STOCK)
            ->execute();
        $this->assertNotNull($holdResponse);
        $this->assertEquals("00", $holdResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease007d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease007e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease008c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease008d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease008e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease009d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease009e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->release()
            ->withReasonCode(ReasonCode::IN_STOCK)
            ->withDescription("JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease010a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease010b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testRelease010c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        $saleResponse = Transaction::fromId(null);

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease010d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease011a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease011b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(null)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease011c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease011d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(null)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease012a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRelease012b()
    {
        $config = $this->getBaseConfig();
        $config->sharedSecret = 'secreto';
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testRelease013a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Hold")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRelease013b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "EC";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testRelease013c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 5000;
        $config->channel = "ECOOOOOOOOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $saleResponse->hold()
            ->withReasonCode(ReasonCode::OTHER)
            ->withDescription("JAVA-Query")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-006a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-006b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-006c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-006d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-006e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006f()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-006f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006g()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-006g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006h()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-006h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006i()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-006i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006j()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-006j")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual006k()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-006k")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual007a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-007a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual007b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-007b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual007c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-007c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual007d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-007d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual007e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-007e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual008a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-008a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual008b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-008b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual008c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-008c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual008d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-008d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual008e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-008e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual009a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOM";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-009a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual009b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "E";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-009b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual009c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 20000;
        $config->channel = "ECOMMERCE";
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-009c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual009d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-009d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual010a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-010a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual010b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-010b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual010c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-010c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual010d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-010d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual010e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-010e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual011a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-011a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual011b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-011b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual011c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-011c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testManual011d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(10)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge()
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-011d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual012a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-012a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual012b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EURO")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EURO")
            ->withDescription("JAVA-Manual-012b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual012c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("�UR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("�UR")
            ->withDescription("JAVA-Manual-012c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     */
    public function testManual012d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // request
        $response = $card->charge(100.01)
            ->withDescription("JAVA-Manual-012d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual013a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-013a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual013b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-013b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual013b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-013b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual013c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-013c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual014a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-014a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual014b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-014b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual014c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep";

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-014c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual014d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cardHolderName = "James~Mason";

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-014d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual015a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-015a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual015b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-015b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual015c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 20;
        $card->expYear = TestCards::expiredCardExpYear();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-015c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual015d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = null;
        $card->expYear = null;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-015d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual016a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-016a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual016b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-016b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual016c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-016c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual017a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-017a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual018a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-018a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual019a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-019a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual019b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->ResponseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-019b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->ResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual019b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->ResponseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-019b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->ResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual019c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12345";

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->ResponseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-019c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->ResponseCode);
    }

    public function testManual019d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374101000000608";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-019d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual020a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-020a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual020a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::ILLEGIBLE;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-020a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual020a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_ON_CARD;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-020a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual020a4()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-020a4")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
    
    public function testManual020b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvnPresenceIndicator = 5;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-020b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual020c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = 0;

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-020c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual021a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-021a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual021a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->Authorize(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-021a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual021a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->Authorize(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-021a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual021b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->Authorize(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-021b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual021c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->Authorize(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-021c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual022a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-022a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual022b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-022b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual022c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-022c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual022d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-022d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual022e()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-022e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual023a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-023a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual023a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-023a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual023b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-023b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual023c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-023c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual024a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-024a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual024a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-024a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual024a3()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-024a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual024b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-024b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual024c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-024c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual025()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-025")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual026a1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-026a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual026a2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual026b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual026c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIep3uviSnW9XEB3a4wpIW9XEB3a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual026c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-026c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual027a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerId("123456")
            ->withDescription("JAVA-Manual-027a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual028a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("123456")
            ->withDescription("JAVA-Manual-028a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual028b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-028b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual028c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Manual-028c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual028d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerId("123456~")
            ->withDescription("JAVA-Manual-028d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual029a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withProductId("123456")
            ->withDescription("JAVA-Manual-029a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual029b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withDescription("JAVA-Manual-029b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual029c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withProductId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Manual-029c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual029d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withProductId("123456~")
            ->withDescription("JAVA-Manual-029d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual030a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withClientTransactionId("123456")
            ->withDescription("JAVA-Manual-030a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual030b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-030b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual030c()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withClientTransactionId("3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep33a4wpQQQQQQQQQ1")
            ->withDescription("JAVA-Manual-030c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testManual030d()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withClientTransactionId("123456~")
            ->withDescription("JAVA-Manual-030d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual031a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerIpAddress("123.123.123.123")
            ->withDescription("JAVA-Manual-031a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual031b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-031b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual031c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withCustomerIpAddress("1234.123.123.123")
            ->withDescription("JAVA-Manual-031c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual031c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withCustomerIpAddress("123~.123.123.123")
            ->withDescription("JAVA-Manual-031c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual032a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "E77 4QJ";
        $billingAddress->country = "United Kingdom";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "Z76 PO9";
        $shippingAddress->country = "France";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-032a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual033a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "774|10";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "769|52";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-033a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual033b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "774|10";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Manual-033b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual033b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "769|52";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-033b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual033c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->postalCode = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Manual-033c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual033c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->postalCode = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-033c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual034a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "FR";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-034a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual034b1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "GB";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-034b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual034b2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "GB";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Manual-034b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual034c1()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // billing address
        $billingAddress = new Address();
        $billingAddress->country = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withAddress($billingAddress)
            ->withDescription("JAVA-Manual-034c1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual034c2()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // shipping address
        $shippingAddress = new Address();
        $shippingAddress->country = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwep4wpIwep3u111";

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("USD")
            ->withAddress($shippingAddress, AddressType::SHIPPING)
            ->withDescription("JAVA-Manual-034c2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual035a()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("GBP")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("GBP")
            ->withDescription("JAVA-Manual-035a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testManual035b()
    {
        $config = $this->getBaseConfig();
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();

        // build transaction
        $saleResponse = $card->charge(100.01)
            ->withCurrency("EUR")
            ->execute();
        $this->assertNotNull($saleResponse);
        $this->assertEquals("00", $saleResponse->responseCode);
        $this->tearDown();

        // request
        $response = $card->charge(100.01)
            ->withCurrency("EUR")
            ->withDescription("JAVA-Manual-035a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001038443335";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001038488884";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001036298889";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001036853337";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037167778";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037484447";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled014i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037490006";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-014i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled015a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000198�";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015b()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000149";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015c()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000172";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015d()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000297";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015e()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000131";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015f()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000206";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015g()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000131";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015h()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000214";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled015i()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "5100000000000164";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-015i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled016a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "370537726695896�";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016b()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "344598846104303";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016c()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "342911579886552";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016d()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "377775599797356";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016e()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "371810438025523";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016f()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "374973180958759";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016g()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "371810438025523";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016h()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "376515222233960";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled016i()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "372749236937027";
        $card->expMonth = 10;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-016i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-017a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-017b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-017c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-017d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-017e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017f()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-017f")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017g()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-017g")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017h()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-017h")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017i()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-017i")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017j()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-017j")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled017k()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-017k")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled018a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-018a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled018b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-018b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled018c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-018c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled018d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-018d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled018e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-018e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled019a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-019a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled019b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-019b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled019c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-019c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled019d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-019d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled019e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-019e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled020b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-020b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled020c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-020c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled020d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-020d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled020e()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-020e")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled021a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-021a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled021b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-021b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled021c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-021c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled021d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-021d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled022a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-022a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled022b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EURO")
            ->withDescription("JAVA-verifyenrolled-022b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled022c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("�UR")
            ->withDescription("JAVA-verifyenrolled-022c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled022d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withDescription("JAVA-verifyenrolled-022d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled023a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-023a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled023b1()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-023b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled023b2()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "42424242424";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-023b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled023c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4263970000005262#";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-023c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled024a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->CvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-024a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled024b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-024b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled024c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;
        $card->cardHolderName = "3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep3a4wpIwep3uviSnW9XEB3a4wpIwep3uviSnW9XEB3a4wpIwepeep";

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-024c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled024d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;
        $card->cardHolderName = "James~Mason";

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-024d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled025a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-025a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled025b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-025b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled025c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 20;
        $card->expYear = TestCards::expiredCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-025c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled025d()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-025d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled026a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-026a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled026b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-026b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled026c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-026c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled027a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-027a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled028a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-028a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled029a()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-029a")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled029b1()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-029b1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled029b2()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "371810438025523";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-029b2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled029c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "12345";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-029c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled029d()
    {
        $this->markTestSkipped();

        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "371810438025523";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "1234";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-029d")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled030a1()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-030a1")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled030a2()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::ILLEGIBLE;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-030a2")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled030a3()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_ON_CARD;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-030a3")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled030a4()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "";
        $card->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;

        // request
        $response = $card->verify()
            ->withCurrency("GBP")
            ->withDescription("JAVA-verifyenrolled-030a4")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function testverifyenrolled030b()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = 5;

        // request
        $response = $card->verify()
            ->withCurrency("EUR")
            ->withDescription("JAVA-verifyenrolled-030b")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function testverifyenrolled030c()
    {
        $config = $this->getBaseConfig();
        $config->timeout = 60000;
        ServicesContainer::configureService($config);

        // create card
        $card = $this->getBaseCardData();
        $card->number = "4012001037141112";
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = "123";
        $card->cvnPresenceIndicator = 0;

        // request
        $response = $card->verify()
            ->withCurrency("USD")
            ->withDescription("JAVA-verifyenrolled-030c")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
