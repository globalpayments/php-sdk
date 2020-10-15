<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class TransITAdminTest extends TestCase
{
    protected $card;

    public function setup()
    {
        $this->card = TestCards::visaManual();
        
        ServicesContainer::configure($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322602';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
        $config->developerId = '003226G001';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        
        return $config;
    }
    
    public function testTokenizeCardKeyed()
    {
        $token = $this->card->tokenize()->execute();
        $this->assertNotNull($token);
        $this->assertEquals('00', $token->responseCode);
        $this->assertNotNull($token->token);
    }
    
    public function testCreateManifest()
    {
        $config = new ServicesConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322602';
        $config->developerId = '003226G001';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        
        ServicesContainer::configure($config);
        $provider = ServicesContainer::instance()->getClient();
        
        //create Transaction Key
        $response = $provider->getTransactionKey();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->transactionKey);
        
        //create Manifest
        $provider->transactionKey = $response->transactionKey;
        $manifest = $provider->createManifest();
        
        $this->assertNotNull($manifest);
    }
    
    public function testDisableTransactionKey()
    {
        $config = new ServicesConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322602';
        $config->developerId = '003226G001';
         //TransactionKey needs to be disabled. Throw 'Invalid Transaction Key' when key is not in active state
        $config->transactionKey = 'F508Z7TIGFORSTDYJQLMK9NGFFPBIXV0';
        $config->gatewayProvider = GatewayProvider::TRANSIT;
        
        ServicesContainer::configure($config);
        $provider = ServicesContainer::instance()->getClient();
        
        //create new Transaction Key
        $response = $provider->getTransactionKey();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->transactionKey);
    }
}
