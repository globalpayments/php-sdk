<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class EbtCardTest extends TestCase
{
    private $card;

    private $track;

    private $amount = 10;
    private $currency = 'USD';

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = TestCards::asEBTManual(TestCards::visaManual(true), '32539F50C245A6A93D123412324000AA');
        $this->track = TestCards::asEBTTrack(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
    }

    public function testEbtSale_Manual()
    {
        $this->card->cardHolderName = 'Jane Doe';

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtSale_Swipe()
    {
        $response = $this->track->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtRefund()
    {
        $this->card->cardHolderName = 'Jane Doe';

        $response = $this->card->refund($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtSale_Refund_Swipe()
    {
        $response = $this->track->refund($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtTransactionRefund()
    {
        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $response = $transaction->refund()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtTransactionRefund_TrackData()
    {
        $transaction = $this->track->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $response = $transaction->refund()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testEbtTransaction_Reverse()
    {
        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $response = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::REVERSED);
    }

    public function testEbtTransaction_Reverse_TrackData()
    {
        $transaction = $this->track->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $response = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertEbtTransactionResponse($response, TransactionStatus::REVERSED);
    }

    private function assertEbtTransactionResponse($transactionResponse, $transactionStatus)
    {
        $this->assertNotNull($transactionResponse);
        $this->assertEquals('SUCCESS', $transactionResponse->responseCode);
        $this->assertEquals($transactionStatus, $transactionResponse->responseMessage);
    }

}