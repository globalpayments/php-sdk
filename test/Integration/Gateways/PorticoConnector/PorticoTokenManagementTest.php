<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class PorticoTokenManagementTest extends TestCase
{
    protected $token;
    
    public function setup()
    {
        ServicesContainer::configureService($this->getConfig());

        try {
            $card = new CreditCardData();
            $card->number = '4111111111111111';
            $card->expMonth = 12;
            $card->expYear = TestCards::validCardExpYear();
            $card->cvn = '123';

            $this->token = $card->tokenize()->execute()->token;
            $this->assertTrue(!empty($this->token), 'TOKEN COULD NOT BE GENERATED.');
        } catch (ApiException $exc) {
            $this->fail($exc->getMessage());
        }
    }
        
    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        return $config;
    }

    public function testUpdateToken()
    {
        $token = new CreditCardData();
        $token->token = $this->token;
        $token->expMonth = 12;
        $token->expYear = TestCards::validCardExpYear();

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
            $this->assertTrue(false, 'Expected exception');
        } catch (GatewayException $exc) {
            $this->assertEquals('23', $exc->responseCode);
        }
    }
}
