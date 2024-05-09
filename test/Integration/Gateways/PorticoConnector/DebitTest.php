<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\Genius\Entities\Enums\TransactionIdType;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class DebitTest extends TestCase
{
    protected $track;
    private $enableCryptoUrl = true;

    public function setup(): void
    {
        $this->track = TestCards::asDebit(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        ServicesContainer::configureService($this->getConfig());
    }

    public function testDebitSale()
    {
        $response = $this->track->charge(17.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testDebitAddValue()
    {
        $this->markTestSkipped('GSB not configured');

        $response = $this->track->addValue(15.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testDebitRefund()
    {
        $response = $this->track->refund(16.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testDebitReverseUsingFromId()
    {
        $response = $this->track->charge(17.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        
        $reversalResponse = Transaction::fromId($response->transactionId, PaymentMethodType::DEBIT)
                    ->reverse(17.01)
                    ->execute();

        $this->assertNotNull($reversalResponse );
        $this->assertEquals('00',  $reversalResponse->responseCode);
        $this->assertEquals("APPROVAL", $reversalResponse->responseMessage);

    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MaePAQBr-1QAqjfckFC8FTbRTT120bVQUlfVOjgCBw';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }
}
