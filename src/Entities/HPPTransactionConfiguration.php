<?php
namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\{
    CaptureMode,
    Channel,
    HPPAllowedPaymentMethods,
    PaymentMethodUsageMode
};
use GlobalPayments\Api\Utils\StringUtils;

/**
 * HPPTransactionConfiguration entity class for hosted payment page requests
 * 
 * Contains settings that control how transactions are processed
 * including channel, country, capture mode, and allowed payment methods
 */
class HPPTransactionConfiguration
{
    /**
     * Describes whether the transaction was processed in a Card present (CP) scenario 
     * or a card Not Present (CNP) transaction.
     * 
     * @var string
     */
    public ?string $channel = null;
    
    /**
     * The country in ISO-3166-1 (alpha-2 code) format.
     * 
     * @var string
     */
    public ?string $country = null;
    
    /**
     * Indicates whether the transaction is to be captured automatically or later.
     * Values from CaptureMode enum: AUTO, LATER, MULTIPLE
     * 
     * @var string
     */
    public ?string $captureMode = null;
    
    /**
     * Determines whether Dynamic Currency Conversion is enabled.
     * 
     * @var bool|null
     */
    public ?bool $currencyConversionMode = null;
    
    /**
     * The payment methods available to create transactions with.
     * Values from HPPAllowedPaymentMethods enum: CARD, BANK_PAYMENT, BLIK
     * 
     * @var array
     */
    public ?array $allowedPaymentMethods = null;
    
    /**
     * Indicates whether the link can be used once or multiple times.
     * Values from PaymentMethodUsageMode enum: SINGLE, MULTIPLE
     * 
     * @var string
     */
    public ?string $usageMode = null;
    
    /**
     * The number of times that the link can be used
     * 
     * @var string
     */
    public ?string $usageLimit = null;
    
    /**
     * Validate transaction configuration
     * 
     * @return errors array List of validation errors, empty if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate channel
        if (empty($this->channel)) {
            $errors[] = 'Channel is required';
        } else {
            $reflection = new \ReflectionClass(Channel::class);
            $validValues = array_values($reflection->getConstants());
            if (!in_array($this->channel, $validValues)) {
                $errors[] = 'Invalid channel value';
            }
        }
        
        // Validate country
        if (empty($this->country)) {
            $errors[] = 'Country is required';
        } elseif (strlen($this->country) !== 2) {
            $errors[] = 'Country must be in ISO-3166-1 (alpha-2 code) format';
        }
        
        // Validate captureMode
        if (!empty($this->captureMode)) {
            $reflection = new \ReflectionClass(CaptureMode::class);
            $validValues = array_values($reflection->getConstants());
            if (!in_array($this->captureMode, $validValues)) {
                $errors[] = 'Invalid captureMode value';
            }
        }
        
        // Validate currencyConversionMode
        if (!is_null($this->currencyConversionMode) && !is_bool($this->currencyConversionMode)) {
            $errors[] = 'currencyConversionMode must be a boolean value';
        }
        
        // Validate usageMode
        if (!empty($this->usageMode)) {
            try {
                PaymentMethodUsageMode::validate($this->usageMode);
            } catch (\Exception $e) {
                $errors[] = 'Invalid usageMode value';
            }
        }
        
        // Validate usageLimit
        if (!empty($this->usageLimit) && !is_numeric($this->usageLimit)) {
            $errors[] = 'Usage limit must be a number';
        }
        
        // Validate allowedPaymentMethods
        if (!empty($this->allowedPaymentMethods)) {
            foreach ($this->allowedPaymentMethods as $method) {
                try {
                    HPPAllowedPaymentMethods::validate($method);
                } catch (\Exception $e) {
                    $errors[] = "Invalid payment method: {$method}";
                }
            }
        }
        
        return $errors;
    }
}
