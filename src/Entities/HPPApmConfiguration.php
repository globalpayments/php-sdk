<?php
/**
 * APM Configuration for Hosted Payment Pages, goes in order.payment_method_configuration.amp
 * 
 */

namespace GlobalPayments\Api\Entities;

/**
 * Configuration class for AMP's in hosted payment pages
 * These properties are PayPal specific. From the Documentation:
 * shippingAddressEnabled - This field determines whether the passing of PayPal shipping address details will be activated or not
 * addressOverride - Determines whether the shipping address can be changed by the customer on the PayPal review page
 */
class HPPApmConfiguration
{
    /**
     * Determines whether shipping address passing will be activated for PayPal
     * @var bool|null
     */
    public ?bool $shippingAddressEnabled = false;
    
    /**
     * Determines whether the shipping address can be changed by the customer on the PayPal review page
     * @var bool|null
     */
    public ?bool $addressOverride = false;
    
    /**
     * Validate APM configuration
     * @return array List of validation errors, empty if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate shippingAddressEnabled, if provided
        if (!is_null($this->shippingAddressEnabled) && !is_bool($this->shippingAddressEnabled)) {
            $errors[] = 'shippingAddressEnabled must be a boolean value';
        }
        
        // Validate addressOverride, if provided
        if (!is_null($this->addressOverride) && !is_bool($this->addressOverride)) {
            $errors[] = 'addressOverride must be a boolean value';
        }
        
        return $errors;
    }
}
