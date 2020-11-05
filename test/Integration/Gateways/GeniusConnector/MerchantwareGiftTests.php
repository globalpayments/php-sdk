<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GeniusConnector;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GeniusConfig;

class GiftTest extends TestCase
{
    protected $card;
    protected $track;

    public function setup() : void
    {
        ServicesContainer::configureService($this->getConfig());

        $this->card = new GiftCard();
        $this->card->number = '6033590009112245098';
        $this->card->pin = '';
                
        $this->track = new GiftCard();
        $this->track->trackData = ';6033590009112245098=64120000000000000?';
    }

    protected function getConfig()
    {
        $config = new GeniusConfig();
        $config->merchantName = 'Test Shane Logsdon';
        $config->merchantSiteId = 'BKHV2T68';
        $config->merchantKey = 'AT6AN-ALYJE-YF3AW-3M5NN-UQDG1';
        $config->gatewayProvider = GatewayProvider::GENIUS;
        $config->environment = Environment::TEST;
        return $config;
    }

    public function testGiftActivateCard()
    {
        $response = $this->card->activate(10)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    
    public function testGiftAddValue()
    {
        $response = $this->track->addValue(10)
        ->withCurrency('USD')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftBalanceInquiry()
    {
        $response = $this->card->balanceInquiry()
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftSale()
    {
        $response = $this->track->charge(10)
        ->withCurrency('USD')
        ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftRefund()
    {
        $response = $this->track->charge(10)
        ->withCurrency('USD')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $transaction = $response->fromId($response->transactionReference->transactionId, null, paymentMethodType::GIFT);
        $response = $transaction->refund(10)
        ->withCurrency('USD')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftVoid()
    {
        $response = $this->track->charge(10)
        ->withCurrency('USD')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $transaction = $response->fromId($response->transactionReference->transactionId, null, paymentMethodType::GIFT);
        $response = $transaction->void()
        ->withCurrency('USD')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftAddCurrencyPoints()
    {
        $response = $this->track->rewards(10)
        ->withCurrency('Currency')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testGiftAddPoints()
    {
        $response = $this->track->rewards(10)
        ->withCurrency('Points')
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
}
