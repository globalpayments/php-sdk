<?php


namespace Certifications;


use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class GpApiSdkCertificationTest extends TestCase
{
    /**
     * @var CreditCardData $card
     */
    private $card;

    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";
        $this->card->cvnPresenceIndicator = CvnPresenceIndicator::PRESENT;
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        //this is gpapistuff stuff
        $config->appId = 'i872l4VgZRtSrykvSn8Lkah8RE1jihvT';
        $config->appKey = '9pArW2uWoA8enxKc';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardNotPresent;

        return $config;
    }

    public function testCreditCard_Visa_Success()
    {
        $this->card->number = "4263970000005262";

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Visa_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('VISA', $response->cardType);
        $this->assertEquals('00', $response->authorizationCode);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_Mastercard_Success()
    {
        $this->card->number = '5425230000004415';

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Mastercard_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('MASTERCARD', $response->cardType);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_AmericanExpress_Success()
    {
        $this->card->number = '374101000000608';
        $this->card->cvn = "1234";

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_AmericanExpress_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('AMEX', $response->cardType);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_DinersClub_Success()
    {
        $this->card->number = '36256000000725';

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_DinersClub_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DINERS', $response->cardType);
        $this->assertEquals('00', $response->authorizationCode);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_Discover_Success()
    {
        $this->card->number = '6011000000000087';

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Discover_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DISCOVER', $response->cardType);
        $this->assertEquals('00', $response->authorizationCode);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_JCB_Success()
    {
        $this->card->number = '3566000000000000';

        $response = $this->card->charge(15)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_JCB_Success")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('JCB', $response->cardType);
        $this->assertEquals('00', $response->authorizationCode);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditCard_Visa_Declined_101()
    {
        $this->card->number = '4000120000001154';

        $response = $this->card->charge(30)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Visa_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('VISA', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseCode);
    }

    public function testCreditCard_Visa_Declined_102()
    {
        $this->card->number = '4000130000001724';

        $response = $this->card->charge(12)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Visa_Declined_102")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('VISA', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Visa_Declined_103()
    {
        $this->card->number = '4000160000004147';

        $response = $this->card->charge(12)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Visa_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('VISA', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Mastercard_Declined_101()
    {
        $this->card->number = '5114610000004778';

        $response = $this->card->charge(12)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Mastercard_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('MASTERCARD', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Mastercard_Declined_102()
    {
        $this->card->number = '5114630000009791';

        $response = $this->card->charge(15)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Mastercard_Declined_102")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('MASTERCARD', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Mastercard_Declined_103()
    {
        $this->card->number = '5121220000006921';
        $this->card->cvnPresenceIndicator = CvnPresenceIndicator::ILLEGIBLE;

        $response = $this->card->charge(27)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Mastercard_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('MASTERCARD', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_AmericanExpress_Declined_101()
    {
        $this->card->number = '376525000000010';
        $this->card->cvn = '1234';

        $response = $this->card->charge(17)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_AmericanExpress_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('AMEX', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_AmericanExpress_Declined_102()
    {
        $this->card->number = '375425000000907';
        $this->card->cvn = '1234';

        $response = $this->card->charge(17)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_AmericanExpress_Declined_102")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('AMEX', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_AmericanExpress_Declined_103()
    {
        $this->card->number = '343452000000306';
        $this->card->cvn = '1234';

        $response = $this->card->charge(17)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_AmericanExpress_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('AMEX', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_DinersClub_Declined_101()
    {
        $this->card->number = '36256000000998';

        $response = $this->card->charge(17)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_DinersClub_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DINERS', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_DinersClub_Declined_102()
    {
        $this->card->number = '36256000000634';

        $response = $this->card->charge(17)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_DinersClub_Declined_102")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DINERS', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_DinersClub_Declined_103()
    {
        $this->card->number = '38865000000705';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_DinersClub_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DINERS', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Discover_Declined_101()
    {
        $this->card->number = '6011000000001010';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Discover_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DISCOVER', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Discover_Declined_102()
    {
        $this->card->number = '6011000000001028';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Discover_Declined_102")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DISCOVER', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Discover_Declined_103()
    {
        $this->card->number = '6011000000001036';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_Discover_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('DISCOVER', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_JCB_Declined_101()
    {
        $this->card->number = '3566000000001016';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_JCB_Declined_101")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('JCB', $response->cardType);
        $this->assertEquals('101', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_JCB_Declined_102()
    {
        $this->card->number = '3566000000001024';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_JCB_Declined_102")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('JCB', $response->cardType);
        $this->assertEquals('102', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_JCB_Declined_103()
    {
        $this->card->number = '3566000000001032';

        $response = $this->card->charge(20)
            ->withCurrency("USD")
            ->WithDescription("CreditCard_JCB_Declined_103")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('JCB', $response->cardType);
        $this->assertEquals('103', $response->authorizationCode);
        $this->assertEquals(TransactionStatus::DECLINED, $response->responseMessage);
    }

    public function testCreditCard_Visa_Processing_Error()
    {
        $this->card->number = '4009830000001985';
        try {
            $response = $this->card->charge(17)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_Visa_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_Visa_Processing_Error_Wrong_Currency()
    {
        $this->card->number = '4009830000001985';
        try {
            $response = $this->card->charge(17)
                ->withCurrency("XXX")
                ->WithDescription("CreditCard_Visa_Processing_Error_Wrong_Currency")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50024", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_Mastercard_Processing_Error()
    {
        $this->card->number = '5135020000005871';
        try {
            $response = $this->card->charge(17)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_Mastercard_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_AmericanExpress_Processing_Error()
    {
        $this->card->number = '372349000000852';
        $this->card->cvn = '1234';

        try {
            $response = $this->card->charge(17)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_AmericanExpress_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_DinersClub_Processing_Error()
    {
        $this->card->number = '30450000000985';

        try {
            $response = $this->card->charge(17)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_DinersClub_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_Discover_Processing_Error()
    {
        $this->card->number = '6011000000002000';

        try {
            $response = $this->card->charge(17)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_Discover_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }

    public function testCreditCard_JCB_Processing_Error()
    {
        $this->card->number = '3566000000002006';

        try {
            $response = $this->card->charge(4.99)
                ->withCurrency("USD")
                ->WithDescription("CreditCard_JCB_Processing_Error")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals("50013", $e->responseCode);
            $this->assertContains("SYSTEM_ERROR_DOWNSTREAM", $e->getMessage());
        }
    }
}