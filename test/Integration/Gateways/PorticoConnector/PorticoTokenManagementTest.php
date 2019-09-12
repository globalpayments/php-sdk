<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use PHPUnit\Framework\TestCase;

class PorticoReportingTests extends TestCase
{
    protected $token;
    
    public function setup() : void
    {
        ServicesContainer::configure($this->getConfig());

        try {
            $card = new CreditCardData();
            $card->number = '4111111111111111';
            $card->expMonth = 12;
            $card->expYear = 2025;
            $card->cvn = '123';

            $this->token = $card->tokenize()->execute()->token;
            $this->assertTrue(!empty($this->token), 'TOKEN COULD NOT BE GENERATED.');
        } catch (ApiException $exc) {
            $this->fail($exc->message);
        }
      
    }
        
    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        return $config;
    }

    public function testUpdateToken()
    {
        $token = new CreditCardData();
        $token->token = $this->token;
        $token->expMonth = 12;
        $token->expYear = 2025;

        $this->assertTrue($token->updateTokenExpiry());

        // should succeed
        $response = $token->verify()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testDeleteToken()
    {
        $token = new CreditCardData();
        $token->token = $this->token;

        $this->assertTrue($token->deleteToken());

        // should fail
        try {
            $response = $token->verify()->execute();
        } catch (GatewayException $exc) {
            $this->assertEquals('27', $exc->responseCode);
        }
    }
}