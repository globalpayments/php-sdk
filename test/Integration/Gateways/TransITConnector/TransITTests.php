<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class TransITTests extends TestCase
{
    protected $address;
    protected $card;
    protected $tokenizedCard;
    protected $track;

    public function setup() : void
    {
        ServicesContainer::configureService($this->getConfig());

        $this->address = new Address();
        $this->address->streetAddress1 = '1 Federal Street';
        $this->address->postalCode = '02110';

        $this->card = TestCards::transitVisaManual();

        $this->track = new CreditTrackData();
        $this->track->setValue('<E1050711%4012002000060016^VI TEST CREDIT^25121011803939600000?|LO04K0WFOmdkDz0um+GwUkILL8ZZOP6Zc4rCpZ9+kg2T3JBT4AEOilWTI|+++++++Dbbn04ekG|11;4012002000060016=25121011803939600000?|1u2F/aEhbdoPixyAPGyIDv3gBfF|+++++++Dbbn04ekG|00|||/wECAQECAoFGAgEH2wYcShV78RZwb3NAc2VjdXJlZXhjaGFuZ2UubmV0PX50qfj4dt0lu9oFBESQQNkpoxEVpCW3ZKmoIV3T93zphPS3XKP4+DiVlM8VIOOmAuRrpzxNi0TN/DWXWSjUC8m/PI2dACGdl/hVJ/imfqIs68wYDnp8j0ZfgvM26MlnDbTVRrSx68Nzj2QAgpBCHcaBb/FZm9T7pfMr2Mlh2YcAt6gGG1i2bJgiEJn8IiSDX5M2ybzqRT86PCbKle/XCTwFFe1X|>;');
        
        $this->tokenizedCard = new CreditCardData();
        $this->tokenizedCard->token = '5RpF5t9Asb9U6527'; #gitleaks:allow
    }

    protected function getConfig()
    {
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9'; #gitleaks:allow
        $config->deviceId = '88700000322602';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2'; #gitleaks:allow
        $config->developerId = '003226G001';
        $config->acceptorConfig = new AcceptorConfig();
        return $config;
    }
    
    public function testAdjustTip()
    {
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $adjust = $response->edit()
            ->withGratuity(1.05)
            ->withTaxType(TaxType::NOT_USED)
            ->execute();
        
        $this->assertNotNull($adjust);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testAuthorizeKeyed()
    {
        $response = $this->card->authorize(10)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertFalse($response->avsResponseCode == '0'); // verify an AVS response of some sort
    }
    
    public function testAuthorizeSwiped()
    {
        $response = $this->track->authorize(100)
            ->withCurrency('USD')
            ->withInvoiceNumber('1264')
            ->withClientTransactionId(uniqid('txn', true))
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * Tokenizes TestCards::transitVisaManual(), asserts the tokenization succeeded,
     * and returns the resulting token-only CreditCardData ready for charging.
     */
    private function createTokenizedCard(): CreditCardData
    {
        $card = TestCards::transitVisaManual();
        $token = $card->tokenize()->execute();
        $this->assertNotNull($token);
        $this->assertEquals('00', $token->responseCode);
        $this->assertNotEmpty($token->token);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $token->token;
        return $tokenizedCard;
    }

    public function testAuthorizeToken()
    {
        $tokenizedCard = $this->createTokenizedCard();

        $response = $tokenizedCard->authorize(10)
            ->withCurrency('USD')
            ->withInvoiceNumber('1558')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
        
    public function testTokenizedCardSale()
    {
        $tokenizedCard = $this->createTokenizedCard();

        $response = $tokenizedCard->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testBalanceInquiry()
    {
        $response = $this->card->balanceInquiry()
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testCapture()
    {
        $response = $this->card->authorize(10)
            ->withCurrency('USD')
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $captureResponse = $response->capture()->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }
    
    public function testSaleKeyed()
    {
        
        $response = $this->card->charge(100)
            ->withCurrency('USD')
            ->withAllowDuplicates(false)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testSaleSwiped()
    {
        $response = $this->track->charge(100)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testSaleToken()
    {
        $tokenizedCard = $this->createTokenizedCard();

        $response = $tokenizedCard->charge(1.29)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->withConvenienceAmount(0)
            ->withInvoiceNumber('1559')
            ->withClientTransactionId(uniqid('txn', true))
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
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->batchSummary);
        $this->assertNotNull($response->batchSummary->totalAmount);
    }
    
    public function testVoid()
    {
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $voidResponse = $response->void()
                ->withDescription('DEVICE_UNAVAILABLE')
                ->execute();

        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }
    
    public function testVerify()
    {
        $response = $this->card->verify()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRequestMUTOnSale() {
        $response = $this->card->charge(12.34)
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($response->token);
    }
    
    public function testRefund()
    {
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $refundResponse = $response->refund()
                ->withCurrency('USD')
                ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->responseCode);
    }
    
    public function testRefundByCard()
    {
        $response = $this->card->refund(15.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testRefundBySwipe()
    {
        $response = $this->track->refund(15.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testSaleSwipedTrack1Pattern()
    {
        $this->track = new CreditTrackData();
        $this->track->setValue('%B5473500000000014^MC TEST CARD^251210199998888777766665555444433332');
        
        $response = $this->track->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testAuthorizeWithoutAmount()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage('amount must be a positive numeric value for this transaction type.');

        $response = $this->card->authorize()
            ->withCurrency('USD')
            ->execute();
    }
    
    public function testSaleWithoutAmount()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage('amount must be a positive numeric value for this transaction type.');

        $response = $this->card->charge()
            ->withCurrency('USD')
            ->execute();
    }
    
    public function testRefundWithoutAmount()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage('amount must be a positive numeric value for this transaction type.');

        $response = $this->card->refund()
            ->withCurrency('USD')
            ->execute();
    }
    
    public function testCredentialsError()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\ConfigurationException::class);
        $this->expectExceptionMessage('DeviceId cannot be null.');

        $config = new TransitConfig();
        $config->acceptorConfig = new AcceptorConfig();

        ServicesContainer::configureService($config);
    }
}
