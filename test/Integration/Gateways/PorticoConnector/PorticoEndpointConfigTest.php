<?php

declare(strict_types=1);

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;
use PHPUnit\Framework\TestCase;

class PorticoEndpointConfigTest extends TestCase
{
    private object $requestLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock request logger that captures URLs
        $this->requestLogger = new class implements \GlobalPayments\Api\Entities\IRequestLogger {
            public static array $urls = [];
            
            public function requestSent($verb, $endpoint, $headers, $queryStringParams, $data): void {
                self::$urls[] = $endpoint;
                error_log("REQUEST CAPTURED: $verb $endpoint");
            }
            
            public function responseReceived(\GlobalPayments\Api\Gateways\GatewayResponse $response): void {}
            
            public function responseError(\Exception $e, $headers = ''): void {}
        };
        
        // Clear previous URLs for this test
        $this->requestLogger::$urls = [];
    }

    /**
     * CRITICAL: Verify production credentials with explicit CERT endpoint route to CERT
    */
    public function testProdKeyWithCertEndpoint(): void
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_prod_9UnjAQz7gI5Bnl6MQq'; #gitleaks:allow
        $config->serviceUrl = ServiceEndpoints::PORTICO_TEST;
        $config->requestLogger = $this->requestLogger;
        
        ServicesContainer::configureService($config, 'cert_test');

        try {
            $this->createTestCard()->charge(10.00)
                ->withCurrency('USD')
                ->withAddress($this->createTestAddress())
                ->execute('cert_test');
        } catch (\Exception) {
            // Expected to fail - we're testing URL routing
        }

        $capturedUrl = $this->requestLogger::$urls[0] ?? '';
        $this->assertStringContainsString('cert.api2.heartlandportico.com', $capturedUrl);
        $this->assertStringNotContainsString('https://api2.heartlandportico.com/', $capturedUrl);
    }

    /**
     * Verify that production credentials without explicit URL go to production
    */
    public function testProdKeyAutoConfig(): void
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_prod_9UnjAQz7gI5Bnl6MQq'; #gitleaks:allow
        $config->requestLogger = $this->requestLogger;
        
        ServicesContainer::configureService($config, 'prod_auto');

        try {
            $this->createTestCard()
                ->charge(10.00)
                ->withCurrency('USD')
                ->execute('prod_auto');
        } catch (\Exception) {
            // Expected to fail - we're testing URL routing
        }

        $capturedUrl = $this->requestLogger::$urls[0] ?? '';
        $this->assertStringNotContainsString('cert.', $capturedUrl);
        $this->assertStringContainsString('api2.heartlandportico.com', $capturedUrl);
    }

    /**
     * Critical bug scenario: Production credentials + explicit CERT must NOT hit production
    */
    public function testBugFix(): void
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_prod_9UnjAQz7gI5Bnl6MQq'; #gitleaks:allow
        $config->serviceUrl = ServiceEndpoints::PORTICO_TEST;
        $config->requestLogger = $this->requestLogger;
        
        ServicesContainer::configureService($config, 'bug_scenario');

        try {
            $this->createTestCard()->charge(1.00)
                ->withCurrency('USD')
                ->withAddress($this->createTestAddress())
                ->execute('bug_scenario');
        } catch (\Exception) {
            // Expected to fail - we're testing routing, not success
        }

        $actualUrl = $this->requestLogger::$urls[0] ?? '';
        
        $this->assertStringContainsString('cert.api2.heartlandportico.com', $actualUrl);
        $this->assertStringNotContainsString('https://api2.heartlandportico.com/', $actualUrl);
        $this->assertNotEquals(
            ServiceEndpoints::PORTICO_PRODUCTION . '/Hps.Exchange.PosGateway/PosGatewayService.asmx', 
            $actualUrl
        );
    }

    private function createTestCard(): CreditCardData
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cvn = '123';
        
        return $card;
    }

    private function createTestAddress(): Address
    {
        $address = new Address();
        $address->streetAddress1 = '123 Main St';
        $address->city = 'Downtown';
        $address->province = 'NJ';
        $address->postalCode = '12345';
        $address->country = 'USA';
        
        return $address;
    }

    protected function tearDown(): void
    {
        $configurations = ['cert_test', 'prod_auto', 'bug_scenario'];
        
        foreach ($configurations as $config) {
            ServicesContainer::removeConfiguration($config);
        }
        
        parent::tearDown();
    }
}