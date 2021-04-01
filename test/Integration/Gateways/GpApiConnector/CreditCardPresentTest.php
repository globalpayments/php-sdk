<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class CreditCardPresentTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF';
        $config->appKey = 'NmGM0kg92z2gA7Og';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardPresent;

        return $config;
    }

    public function testCardPresentWithChipTransaction()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->charge(19)
            ->withCurrency("EUR")
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCardPresentWithSwipeTransaction()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->authorize(16)
            ->withCurrency("EUR")
            ->withOrderId("124214-214221")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testRefundOnCardPresentChipCard()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $tag = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $response = $card->refund(19)
            ->withCurrency("EUR")
            ->withOrderId("124214-214221")
            ->withTagData($tag)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCreditVerification_CardPresent()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->verify()
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);
    }

    public function testCreditVerification_CardPresent_CVNNotMatched()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "852";
        $card->cardHolderName = "James Mason";

        $response = $card->verify()
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('NOT_VERIFIED', $response->responseCode);
        $this->assertEquals("NOT_VERIFIED", $response->responseMessage);
    }

}