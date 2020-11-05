<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class PaxCreditTests extends TestCase
{

    private $device;
    protected $card;
    protected $address;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
        
        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'Joe Smith';
        
        $this->address = new Address();
        $this->address->streetAddress1 = '123 Main St.';
        $this->address->postalCode = '12345';
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
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }
    
    public function testCreditSale()
    {
        $response = $this->device->creditSale(10)
                ->withAllowDuplicates(1)
                ->execute();
                
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    public function testCreditSaleManual()
    {
        $response = $this->device->creditSale(10)
                ->withPaymentMethod($this->card)
                ->withAddress($this->address)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testCreditSaleWithSignatureCapture()
    {
        $response = $this->device->creditSale(20)
                ->withPaymentMethod($this->card)
                ->withAddress($this->address)
                ->withSignatureCapture(1)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testCreditAuth()
    {
        $response = $this->device->creditAuth(10)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
               
        $captureResponse = $this->device->creditCapture(10)
                ->withTransactionId($response->transactionId)
                ->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
    }
    
    public function testCreditAuthManual()
    {
        $response = $this->device->creditAuth(10)
                ->withAllowDuplicates(1)
                ->withPaymentMethod($this->card)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
        
        $captureResponse = $this->device->creditCapture(10)
                ->withTransactionId($response->transactionId)
                ->execute();
        
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
    }
    
    public function testCreditRefund()
    {
        
        $response = $this->device->creditSale(10)
                ->withPaymentMethod($this->card)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
         
        $refundResponse = $this->device->creditRefund(10)
                ->withTransactionId($response->transactionId)
                ->execute();
        
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }
    
    public function testSaleRefund()
    {
        
        $response = $this->device->creditSale(10)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
         
        $refundResponse = $this->device->creditRefund(10)
                ->withTransactionId($response->transactionId)
                ->execute();
        
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }
    
    public function testRefundByCard()
    {
        $response = $this->device->creditRefund(8)
                ->withPaymentMethod($this->card)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    public function testCreditVerify()
    {
        $response = $this->device->creditVerify()
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    public function testCreditVerifyManual()
    {
        $response = $this->device->creditVerify()
                ->withPaymentMethod($this->card)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    public function testTokenize()
    {
        $response = $this->device->creditVerify()
                ->withRequestMultiUseToken(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
    }
        
    public function testCreditVoid()
    {
        $response = $this->device->creditSale(10)
                ->withPaymentMethod($this->card)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
         
        $refundResponse = $this->device->creditVoid()
                ->withTransactionId($response->transactionId)
                ->execute();
        
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testAuthNoAmount()
    {
        $response = $this->device->creditAuth()
                ->withPaymentMethod($this->card)
                ->execute();
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage transactionId cannot be null for this transaction type
     */
    public function testCaptureNoTransactionId()
    {
        $response = $this->device->creditCapture(10)
                ->execute();
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testRefundNoAmount()
    {
        $response = $this->device->creditRefund()
                ->execute();
    }
    
    /*
     * Note: EMV cards needs to be used for this test case
     */
    public function testCreditSaleEMV()
    {
        $response = $this->device->creditSale(10)
                ->withAllowDuplicates(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        //EMV
        $this->assertNotNull($response->applicationPreferredName);
        $this->assertNotNull($response->applicationLabel);
        $this->assertNotNull($response->applicationId);
        $this->assertNotNull($response->applicationCryptogramType);
        $this->assertNotNull($response->applicationCryptogram);
        $this->assertNotNull($response->customerVerificationMethod);
        $this->assertNotNull($response->terminalVerificationResults);
    }
    
    public function testCreditSaleManualSSL()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::SSL_TCP;
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();
        
        $device = DeviceService::create($config);
        
        $response = $device->creditSale(10)
            ->withPaymentMethod($this->card)
            ->withAllowDuplicates(1)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testCreditSaleManualHTTPS()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::HTTPS;
        $config->timeout = 10;
        $config->requestIdProvider = new RequestIdProvider();
        
        $device = DeviceService::create($config);
        
        $response = $device->creditSale(10)
            ->withPaymentMethod($this->card)
            ->withAllowDuplicates(1)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }
    
    public function testCreditSaleWithMerchantFee()
    {
        $this->markTestSkipped('Merchant fee needs to be enabled in the device for this test case');
        $response = $this->device->creditSale(10)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        $this->assertNotNull($response->merchantFee);
    }
}
