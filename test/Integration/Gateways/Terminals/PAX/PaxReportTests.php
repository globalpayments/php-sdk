<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxSearchCriteria;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\TerminalTransactionType;
use GlobalPayments\Api\Terminals\Enums\TerminalCardType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\ReverseEnumMap;

class PaxReportTests extends TestCase
{

    private $device;
    private $authCode;
    private $transactionNumber;
    private $referenceNumber;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
        
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
                
        $response = $this->device->creditSale(10)
                ->withPaymentMethod($card)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $this->transactionNumber = $response->transactionNumber;
        $this->authCode = $response->authorizationCode;
        $this->referenceNumber = $response->referenceNumber;
    }

    public function tearDown()
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function testReportRecordNumber()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::RECORD_NUMBER, 01)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals(1, $response->reportRecordNumber);
    }
    
    public function testReportReferenceNumber()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::REFERENCE_NUMBER, $this->referenceNumber)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals($this->referenceNumber, $response->referenceNumber);
        $this->assertEquals($this->transactionNumber, $response->transactionNumber);
    }
    
    public function testReportTerminalReferenceNumber()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::TERMINAL_REFERENCE_NUMBER, $this->transactionNumber)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals($this->referenceNumber, $response->referenceNumber);
        $this->assertEquals($this->transactionNumber, $response->transactionNumber);
    }
    
    public function testReportTransactionType()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::TRANSACTION_TYPE, TerminalTransactionType::SALE)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals('SALE', $response->transactionType);
    }
    
    public function testReportAuthCode()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::AUTH_CODE, $this->authCode)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals($this->authCode, $response->authorizationCode);
    }
    
    public function testReportCardType()
    {
        
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::CARD_TYPE, TerminalCardType::VISA)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals('VISA', $response->paymentType);
    }
    
    public function testReportMerchantId()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::MERCHANT_ID, 12345)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
    
    public function testReportMerchantName()
    {
        $response = $this->device->localDetailReport()
                ->where(PaxSearchCriteria::MERCHANT_NAME, "CAS")
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
