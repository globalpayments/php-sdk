<?php

/**
 * Integration Test Configuration for HPP API Tests
 * 
 * This file provides configuration and helper utilities for running
 * integration tests against the GP-API for Hosted Payment Pages.
 */

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector;

use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\{Environment, Channel};

class HPPIntegrationTestConfig
{
    /**
     * Set up test configuration for integration tests
     * 
     * @return GpApiConfig
     */
    public static function getTestConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        
        // Use environment variables or fallback to test values
        $config->appId = "YOUR_APP_ID";
        $config->appKey = "YOUR_APP_KEY";
        $config->environment = Environment::TEST;
        $config->country = 'GB';
        $config->channel = Channel::CardNotPresent;
        
        // Enable request logging for debugging (optional)
        $config->requestLogger = null; // Set to a logger instance if needed
        $config->webProxy = null; // Set if using a proxy
        
        return $config;
    }
    
    /**
     * Configure services for integration testing
     * 
     * @return void
     */
    public static function configureTestServices(): void
    {
        $config = self::getTestConfig();
        ServicesContainer::configureService($config);
    }
    
    /**
     * Check if integration tests can be run
     * 
     * @return bool
     */
    public static function canRunIntegrationTests(): bool
    {
        $appId = "YOUR_APP_ID";
        $appKey = "YOUR_APP_KEY";
        
        return !empty($appId) && !empty($appKey) && 
               $appId !== 'YOUR_TEST_APP_ID' && 
               $appKey !== 'YOUR_TEST_APP_KEY';
    }
    
    /**
     * Get test webhook URLs for notifications
     * 
     * @return array
     */
    public static function getTestWebhookUrls(): array
    {
        return [
            'return' =>"https://".$_SERVER['HTTP_HOST'] . "/gp-pbl-final-pull-request/examples/gp-api/hosted-payment-pages/return_url.php",
            'status' =>"https://".$_SERVER['HTTP_HOST'] . "/gp-pbl-final-pull-request/examples/gp-api/hosted-payment-pages/status.php",
            'cancel' =>"https://".$_SERVER['HTTP_HOST'] . "/gp-pbl-final-pull-request/examples/gp-api/hosted-payment-pages/cancel.php",
            'iframe_callback' =>"https://".$_SERVER['HTTP_HOST'] . "/gp-pbl-final-pull-request/examples/gp-api/hosted-payment-pages/iframe_callback.php",
            'iframe_success' =>"https://".$_SERVER['HTTP_HOST'] . "/gp-pbl-final-pull-request/examples/gp-api/hosted-payment-pages/iframe_callback.php"
        ];
    }
    
    /**
     * Generate a unique test reference
     * 
     * @param string $prefix
     * @return string
     */
    public static function generateTestReference(string $prefix = 'TEST'): string
    {
        return $prefix . '_' . date('YmdHis') . '_' . uniqid();
    }
    
    /**
     * Clean up test services
     * 
     * @return void
     */
    public static function cleanupTestServices(): void
    {
        ServicesContainer::configureService(null);
    }
}
