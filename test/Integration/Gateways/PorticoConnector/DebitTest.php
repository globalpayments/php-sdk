<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class DebitTest extends TestCase
{
    protected $track;
    private $enableCryptoUrl = true;

    public function setup()
    {
        $this->track = TestCards::asDebit(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        ServicesContainer::configure($this->getConfig());
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

    public function testDebitReverse()
    {
        $response = $this->track->reverse(17.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
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
