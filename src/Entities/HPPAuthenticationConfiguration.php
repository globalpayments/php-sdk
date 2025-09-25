<?php
namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\{ChallengeRequestIndicator, ExemptStatus};
use GlobalPayments\Api\Utils\StringUtils;


/**
 * Authentication configuration for hosted payment pages
 * 
 * HPPAuthenticationConfiguration entity class for Hosted Payment Page 3DS options, goes in
 * order.payment_method_configuration.authentications
 * Note: Some of the comments are from the documentation
 */
class HPPAuthenticationConfiguration
{
    /**
     * Indicates whether a challenge is requested for this transaction.
     * The Issuer may override whatever preference is specified.
     * 
     * @var string
     */
    public ?string $preference = null;
    
    /**
     * Indicates if any 3DS exemptions apply to this Hosted Payment Page transaction, the only applicable value is "LOW_VALUE"
     * 
     * @var string
     */
    public ?string $exemptStatus = null;
    
    /**
     * This flag is set by the merchant to indicate if the billing address
     * for 3DS authentication of a transaction can be skipped.
     * 
     * @var bool|null
     */
    public ?bool $billingAddressRequired = null;
    
    /**
     * Validate authentication configuration
     * 
     * @return errors array List of validation errors, empty if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate preference, if provided
        if (!empty($this->preference)) {
            try {
                ChallengeRequestIndicator::validate($this->preference);
            } catch (\Exception $e) {
                $errors[] = 'Invalid authentication preference value';
            }
        }
        
        // Validate exemptStatus, if provided. Only Low value is applicable within Hosted Payment Pages
        if (!empty($this->exemptStatus)) {
            if ($this->exemptStatus !== ExemptStatus::LOW_VALUE) {
                $errors[] = 'Invalid exempt status value, can only be "LOW_VALUE" in Hosted Payment Pages';
            }
        }
        
        // Validate billingAddressRequired, if provided
        if (!is_null($this->billingAddressRequired) && !is_bool($this->billingAddressRequired)) {
            $errors[] = 'billingAddressRequired must be a boolean value';
        }
        
        return $errors;
    }
    
    /**
     * Convert to array representation
     * 
     * @return result array
     */
    public function toArray(): array
    {
        $result = [];
        
        if (!empty($this->preference)) {
            $result['preference'] = $this->preference;
        }
        
        if (!empty($this->exemptStatus)) {
            $result['exempt_status'] = $this->exemptStatus;
        }
        
        if (!empty($this->billingAddressRequired)) {
            $result['billing_address_required'] = is_bool($this->billingAddressRequired) 
                ? StringUtils::boolToYesNo($this->billingAddressRequired) 
                : "NO";
        }
        
        return $result;
    }
}
