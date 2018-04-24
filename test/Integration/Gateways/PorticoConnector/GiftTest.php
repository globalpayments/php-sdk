<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class GiftTest extends TestCase
{
    protected $card;
    protected $track;
    private $enableCryptoUrl = true;

    public function setup()
    {
        $this->card = new GiftCard();
        $this->card->number = '5022440000000000007';

        $this->track = new GiftCard();
        $this->track->trackData = '%B5022440000000000098^^391200081613?;5022440000000000098=391200081613?';

        ServicesContainer::configure($this->getConfig());
    }

    public function testGiftCreate()
    {
        $newCard = GiftCard::create('2145550199');
        $this->assertNotNull($newCard);
        $this->assertNotNull($newCard->number);
        $this->assertNotNull($newCard->alias);
        $this->assertNotNull($newCard->pin);
    }

    public function testGiftAddAlias()
    {
        $response = $this->card->addAlias('2145550199')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftAddValue()
    {
        $response = $this->card->addValue(10)
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
        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftDeactivate()
    {
        $response = $this->card->deactivate()
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftRemoveAlias()
    {
        $response = $this->card->removeAlias('2145550199')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftReplace()
    {
        $response = $this->card->replaceWith($this->track)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftReverse()
    {
        $response = $this->card->reverse(10)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGiftRewards()
    {
        $response = $this->card->rewards(10)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MaePAQBr-1QAqjfckFC8FTbRTT120bVQUlfVOjgCBw';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }
}
