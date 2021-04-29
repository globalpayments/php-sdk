<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class EbtCardTest extends TestCase
{
    private $card;

    private $track;

    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = TestCards::asEBTManual(TestCards::visaManual(true), '32539F50C245A6A93D123412324000AA');
        $this->track = TestCards::asEBTTrack(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        //this is gpapistuff stuff
        $config->appId = 'VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF';
        $config->appKey = 'NmGM0kg92z2gA7Og';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardPresent;

        return $config;
    }

    public function testEbtSale_Manual()
    {
        $this->card->cardHolderName = 'Jane Doe';

        $response = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testEbtSale_Swipe()
    {
        $response = $this->track->charge(10)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testEbtRefund()
    {
        $response = $this->card->refund(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testEbtTransactionRefund()
    {
        $transaction = $this->card->charge(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $response = $transaction->refund()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }
}