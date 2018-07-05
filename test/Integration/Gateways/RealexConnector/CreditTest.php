<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\CreditService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Utils\GenerationUtils;

class CreditTest extends TestCase
{
    protected $card;

    public function setup()
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
        $this->card = $card;

        ServicesContainer::configure($this->getConfig());
    }

    public function testCreditAuthorization()
    {
        $authorization = $this->card->authorize(14)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($authorization);
        $this->assertEquals('00', $authorization->responseCode);

        $capture = $authorization->capture(16)
            ->withGratuity(2)
            ->execute();
        $this->assertNotNull($capture);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function testCreditServiceAuth()
    {
        $service = new CreditService(
            $this->getConfig()
        );

        $authorization = $service->authorize(15)
            ->withCurrency('USD')
            ->withPaymentMethod($this->card)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($authorization);
        $this->assertEquals('00', $authorization->responseCode);

        $capture = $service->capture($authorization->transactionReference)
            ->withAmount(17)
            ->withGratuity(2)
            ->execute();
        $this->assertNotNull($capture);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function testCreditSale()
    {
        $response = $this->card->charge(15)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCreditRefund()
    {
        $response = $this->card->refund(16)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testCreditRebate()
    {
        $response = $this->card->charge(17)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);

        $rebate = $response->refund(17)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($rebate);
        $this->assertEquals('00', $rebate->responseCode, $rebate->responseMessage);
    }

    public function testCreditVoid()
    {
        $response = $this->card->charge(15)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);

        $voidResponse = $response->void()->execute();
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode, $voidResponse->responseMessage);
    }

    public function testCreditVerify()
    {
        $response = $this->card->verify()
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->rebatePassword = 'rebate';
        $config->refundPassword = 'refund';
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';
        return $config;
    }
    
    protected function dccSetup()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "apidcc";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        
        ServicesContainer::configure($config);
    }
    
    public function testCreditGetDccInfo()
    {
        $this->dccSetup();
        
        $this->card->number = '4002933640008365';
        $orderId = GenerationUtils::generateOrderId();
        
        $dccDetails = $this->card->getDccRate(DccRateType::SALE, 10, 'USD', DccProcessor::FEXCO, $orderId);
       
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
    }
    
    public function testCreditDccRateAuthorize()
    {
        $this->dccSetup();
        
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();
        
        $dccDetails = $this->card->getDccRate(DccRateType::SALE, 1001, 'EUR', DccProcessor::FEXCO, $orderId);
        
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
      
        //set Currency conversion rates
        $dccValues = new DccRateData();
        $dccValues->orderId = $dccDetails->transactionReference->orderId;
        $dccValues->dccProcessor = DccProcessor::FEXCO;
        $dccValues->dccType = 1;
        $dccValues->dccRateType = DccRateType::SALE;
        $dccValues->currency = $dccDetails->dccResponseResult->cardHolderCurrency;
        $dccValues->dccRate = $dccDetails->dccResponseResult->cardHolderRate;
        $dccValues->amount = $dccDetails->dccResponseResult->cardHolderAmount;
        
        $response = $this->card->authorize(1001)
            ->withCurrency('EUR')
            ->withAllowDuplicates(true)
            ->withDccRateData($dccValues)
            ->withOrderId($orderId)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);
    }
    
    public function testCreditDccRateCharge()
    {
        $this->dccSetup();
        
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();
        
        $dccDetails = $this->card->getDccRate(DccRateType::SALE, 1001, 'EUR', DccProcessor::FEXCO, $orderId);
        
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
      
        //set Currency conversion rates
        $dccValues = new DccRateData();
        $dccValues->orderId = $dccDetails->transactionReference->orderId;
        $dccValues->dccProcessor = DccProcessor::FEXCO;
        $dccValues->dccType = 1;
        $dccValues->dccRateType = DccRateType::SALE;
        $dccValues->currency = $dccDetails->dccResponseResult->cardHolderCurrency;
        $dccValues->dccRate = $dccDetails->dccResponseResult->cardHolderRate;
        $dccValues->amount = $dccDetails->dccResponseResult->cardHolderAmount;
        
        $response = $this->card->charge(1001)
            ->withCurrency('EUR')
            ->withAllowDuplicates(true)
            ->withDccRateData($dccValues)
            ->withOrderId($orderId)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Unexpected Gateway Response: 105 - Cannot find DCC information for that card
     */
    public function testCreditDccInfoNotFound()
    {
        $this->dccSetup();
        
        $this->card->number = '4002933640008365';
        $orderId = GenerationUtils::generateOrderId();
        
        $dccDetails = $this->card->getDccRate(DccRateType::SALE, 10, 'EUR', DccProcessor::FEXCO, $orderId);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Unexpected Gateway Response: 508 - Incorrect DCC information - doesn't correspond to dccrate request
     */
    public function testCreditDccInfoMismatch()
    {
        $this->dccSetup();
        
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();
        
        $dccDetails = $this->card->getDccRate(DccRateType::SALE, 1001, 'EUR', DccProcessor::FEXCO, $orderId);
        
        $this->assertNotNull($dccDetails);
        $this->assertEquals('00', $dccDetails->responseCode, $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccResponseResult);
        
        $dccValues = new DccRateData();
        $dccValues->orderId = $dccDetails->transactionReference->orderId;
        $dccValues->dccProcessor = DccProcessor::FEXCO;
        $dccValues->dccType = 1;
        $dccValues->dccRateType = DccRateType::SALE;
        $dccValues->currency = $dccDetails->dccResponseResult->cardHolderCurrency;
        $dccValues->dccRate = $dccDetails->dccResponseResult->cardHolderRate;
        $dccValues->amount = $dccDetails->dccResponseResult->cardHolderAmount;
        
        $response = $this->card->authorize(100)
            ->withCurrency('EUR')
            ->withAllowDuplicates(true)
            ->withDccRateData($dccValues)
            ->withOrderId($orderId)
            ->execute();
    }
}
