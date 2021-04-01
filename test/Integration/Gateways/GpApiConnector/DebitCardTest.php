<?php


namespace Gateways\GpApiConnector;


use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class DebitCardTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function testDebitSaleSwipe()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $debitCard->pinBlock = '32539F50C245A6A93D123412324000AA';
        $debitCard->entryMethod = EntryMethod::SWIPE;

      $response = $debitCard->charge(18)
        ->withCurrency("EUR")
        ->withAllowDuplicates(true)
        ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testDebitRefundSwipe()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $debitCard->pinBlock = '32539F50C245A6A93D123412324000AA';
        $debitCard->entryMethod = EntryMethod::SWIPE;

        $response = $debitCard->refund(18)
            ->withCurrency("EUR")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testDebitRefundChip()
    {
        $debitCard = new DebitTrackData();
        $debitCard->setTrackData("%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?");
        $debitCard->entryMethod = EntryMethod::SWIPE;
        $tagData = "9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001";
        $response = $debitCard->refund(18)
            ->withCurrency("EUR")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testDebitReverse()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $debitCard->pinBlock = '32539F50C245A6A93D123412324000AA';
        $debitCard->entryMethod = EntryMethod::SWIPE;

        $transaction = $debitCard->charge(18)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $response = $transaction->reverse()
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $response->responseMessage);
    }

    public function testDebitSaleSwipeEncrypted()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $debitCard->pinBlock = '32539F50C245A6A93D123412324000AA';
        $debitCard->entryMethod = EntryMethod::SWIPE;
        $debitCard->encryptionData = EncryptionData::version1();

        $response = $debitCard->charge(18)
            ->withCurrency("EUR")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testDebitSaleSwipeChip()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = ';4024720012345671=18125025432198712345?';
        $debitCard->pinBlock = 'AFEC374574FC90623D010000116001EE';
        $debitCard->entryMethod = EntryMethod::SWIPE;
        $tagData = '82021C008407A0000002771010950580000000009A031709289C01005F280201245F2A0201245F3401019F02060000000010009F03060000000000009F080200019F090200019F100706010A03A420009F1A0201249F26089CC473F4A4CE18D39F2701809F3303E0F8C89F34030100029F3501229F360200639F370435EFED379F410400000019';
        $response = $debitCard->charge(100)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
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
}