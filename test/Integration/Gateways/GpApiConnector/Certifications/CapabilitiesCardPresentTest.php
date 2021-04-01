<?php

namespace Certifications;

use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class CapabilitiesCardPresentTest extends TestCase
{
    private $card;

    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4242424242424242";
        $this->card->expMonth = "09";
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardPresent = true;
        $this->card->cardHolderName = 'Jon Dow';
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'i872l4VgZRtSrykvSn8Lkah8RE1jihvT';
        $config->appKey = '9pArW2uWoA8enxKc';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardPresent;

        return $config;
    }

    public function testDebitSaleWithChipCondition()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = ';4024720012345671=18125025432198712345?';
        $debitCard->pinBlock = 'AFEC374574FC90623D010000116001EE';
        $debitCard->entryMethod = EntryMethod::SWIPE;
        $response = $debitCard->charge(100)
            ->withCurrency("USD")
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testDebitSaleContactlessChip()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $tagData = '82021C008407A0000002771010950580000000009A031709289C01005F280201245F2A0201245F3401019F02060000000010009F03060000000000009F080200019F090200019F100706010A03A420009F1A0201249F26089CC473F4A4CE18D39F2701809F3303E0F8C89F34030100029F3501229F360200639F370435EFED379F410400000019';

        $response = $debitCard->charge(10)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardPresentManual()
    {
        $response = $this->card->charge(10)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardPresentGratuity()
    {
        $response = $this->card->charge(100)
            ->withCurrency("USD")
            ->withGratuity(20)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }
}