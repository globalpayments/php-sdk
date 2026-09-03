<?php
/**
 * APM Configuration for Hosted Payment Pages, goes in order.payment_method_configuration.amp
 * 
 */

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\CashpressoPaymentPlan;

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
     * APM provider configurations, for example Cashpresso payment plans.
     *
     * @var array<int, array<string, mixed>>|null
     */
    public ?array $configurations = null;
    
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

        if ($this->configurations !== null) {
            if (!is_array($this->configurations)) {
                $errors[] = 'configurations must be an array';
            } else {
                foreach ($this->configurations as $index => $configuration) {
                    if (!is_array($configuration)) {
                        $errors[] = "configurations[{$index}] must be an object-like array";
                        continue;
                    }

                    $provider = strtoupper((string) ($configuration['provider'] ?? ''));
                    if ($provider === '') {
                        $errors[] = "configurations[{$index}].provider is required";
                        continue;
                    }

                    if ($provider === 'CASHPRESSO') {
                        $plans = $configuration['payment_plans'] ?? null;
                        if (!is_array($plans) || empty($plans)) {
                            $errors[] = "configurations[{$index}].payment_plans must contain at least one value for CASHPRESSO";
                            continue;
                        }

                        foreach ($plans as $plan) {
                            try {
                                CashpressoPaymentPlan::validate((string) $plan);
                            } catch (\Exception $e) {
                                $errors[] = "Invalid CASHPRESSO payment plan '{$plan}'";
                            }
                        }
                    }
                }
            }
        }
        
        return $errors;
    }

    /**
     * Convert the APM configuration to an array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'shippingAddressEnabled' => $this->shippingAddressEnabled,
            'addressOverride' => $this->addressOverride,
            'configurations' => $this->configurations
        ];
    }
}
