<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GeniusConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\MobilePaymentMethodType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\Services\CreditService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Gateways\GeniusConnector;
use GlobalPayments\Api\ServiceConfigs\Gateways\GeniusConfig;

class CreditTest extends TestCase
{
    protected $address;
    protected $applePay;
    protected $card;
    protected $encryptedCard;
    protected $tokenizedCard;
    protected $track;

    public function setup() : void
    {
        ServicesContainer::configureService($this->getConfig());

        $this->address = new Address();
        $this->address->streetAddress1 = '1 Federal Street';
        $this->address->postalCode = '02110';

        $this->applePay = new CreditCardData();
        $this->applePay->token = 'ew0KCSJ2ZXJzaW9uIjogIkVDX3YxIiwNCgkiZ==';
        $this->applePay->mobileType = MobilePaymentMethodType::APPLEPAY;

        $this->card = TestCards::visaManual();
        $this->tokenizedCard = new CreditCardData();
        $this->tokenizedCard->token = '100000101GC58TDAUFDZ';

        $this->track = TestCards::visaSwipe();
    }

    protected function getConfig()
    {
        $config = new GeniusConfig();
        // $config->merchantName = 'QA TSys Sierra Test';
        // $config->merchantSiteId = 'D7MX8E4N';
        // $config->merchantKey = '5U6HL-J7GHG-28AX1-G5KQH-AEH0G';
        $config->merchantName = 'Test Shane Logsdon';
        $config->merchantSiteId = 'BKHV2T68';
        $config->merchantKey = 'AT6AN-ALYJE-YF3AW-3M5NN-UQDG1';
        // $config->registerNumber = '35';
        // $config->terminalId = '3';
        $config->gatewayProvider = GatewayProvider::GENIUS;
        $config->environment = Environment::TEST;
        return $config;
    }

    // AdjustTip only works with certain credentials
    // Don't pass in cvn
    public function testAdjustTip()
    {
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('APPROVED', $response->responseMessage);

        $adjust = $response->edit()
            ->withGratuity(1.05)
            ->execute();

        $this->assertNotNull($adjust);
        $this->assertEquals('APPROVED', $response->responseMessage);
    }

    public function testAuthorizeKeyed()
    {
        $response = $this->card->authorize(10)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->withInvoiceNumber('1556')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testAuthorizeSwiped()
    {
        $response = $this->track->authorize(10)
            ->withCurrency('USD')
            ->withInvoiceNumber('1264')
            ->withClientTransactionId('137149')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testAuthorizeVault()
    {
        $response = $this->tokenizedCard->authorize(10)
            ->withCurrency('USD')
            ->withInvoiceNumber('1558')
            ->withClientTransactionId('167903')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testBoardCardKeyed()
    {
        $token = $this->card->tokenize()->execute();
        $this->assertNotNull($token);
    }

    public function testBoardCardSwiped()
    {
        $token = $this->track->tokenize()->execute();
        $this->assertNotNull($token);
    }

    public function testCapture()
    {
        $response = $this->track->authorize(10)
            ->withCurrency('USD')
            ->withInvoiceNumber('1264')
            ->withClientTransactionId('137149')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $captureResponse = $response->capture(10)->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testUpdatedBoardedCard()
    {
        $this->tokenizedCard->expMonth = 12;
        $this->tokenizedCard->expYear = TestCards::validCardExpYear();

        $success = $this->tokenizedCard->updateTokenExpiry();
        $this->assertTrue($success);
    }

    public function testForceCaptureKeyed()
    {
        $response = $this->card->authorize(10)
            ->withCurrency('USD')
            ->withOfflineAuthCode('V00546')
            ->withInvoiceNumber('1559')
            ->withClientTransactionId('168901')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRefundKeyed()
    {
        $response = $this->card->refund(4.01)
            ->withCurrency('USD')
            ->withInvoiceNumber('1701')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // TODO: Refund using clientTransactionId not implemented on manage transactions
    // public function testRefundTransactionId() {
    //     $response = $this->card->charge(4.01)
    //         ->withCurrency('USD')
    //         ->withInvoiceNumber('1703')
    //         ->withClientTransactionId('165902')
    //         ->execute();

    //     $this->assertNotNull($response);
    //     $this->assertEquals('00', $response->responseCode);

    //     $refund = $response->refund()
    //         ->withClientTransactionId('165902')
    //         ->execute();

    //     $this->assertNotNull($refund);
    //     $this->Equals('00', $refund->responseCode);
    // }

    public function testRefundSwiped()
    {
        $response = $this->track->refund(4.01)
            ->withCurrency('USD')
            ->withInvoiceNumber('1701')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRefundVault()
    {
        $response = $this->tokenizedCard->refund(1.83)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->withConvenienceAmount(0)
            ->withInvoiceNumber('1559')
            ->withAllowPartialAuth(false)
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testSaleKeyed()
    {
        $response = $this->card->charge(1.05)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->withConvenienceAmount(0)
            ->withInvoiceNumber('12345')
            ->withClientTransactionId('166901')
            ->withAllowPartialAuth(false)
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testSaleSwiped()
    {
        $response = $this->track->charge(1.29)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->withConvenienceAmount(0)
            ->withInvoiceNumber('12345')
            ->withClientTransactionId('138401')
            ->withAllowPartialAuth(false)
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testSaleVault()
    {
        $response = $this->tokenizedCard->charge(1.29)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->withConvenienceAmount(0)
            ->withInvoiceNumber('1559')
            ->withClientTransactionId('166909')
            ->withAllowPartialAuth(false)
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testSaleHealthcare()
    {
        $autoSubstantiation = new AutoSubstantiation();
        $autoSubstantiation->setCopaySubTotal(60);

        $response = $this->card->charge(202)
            ->withCurrency('USD')
            ->withInvoiceNumber('1556')
            ->withClientTransactionId('166901')
            ->withAutoSubstantiation($autoSubstantiation)
            ->withAllowPartialAuth(false)
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Response not mapping correctly
    public function testSettleBatch()
    {
        $response = BatchService::closeBatch();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->batchSummary->status);
    }

    // createElement not working
    public function testUnboardCard()
    {
        $token = TestCards::masterCardManual()->tokenize()->execute();
        $this->assertNotNull($token->token);

        $deleteCard = new CreditCardData();
        $deleteCard->token = $token->token;

        $success = $deleteCard->deleteToken();
        $this->assertTrue($success);
    }

    public function testVoid()
    {
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $voidResponse = $response->void()->execute();

        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }
}
