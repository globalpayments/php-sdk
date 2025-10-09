<?php
//HPPNotifications class for hosted payment callbacks, this goes in the root of the Hosted Payment Page request.

namespace GlobalPayments\Api\Entities;

class HPPNotifications
{
    /**
     *  URL to display after payment is completed, should create a form to submit the payment details to the final URL
     * 
     * @var string|null
     */
    public ?string $returnUrl = null;
    
    /**
     * URL for receiving status updates, when certain payment events occur
     * 
     * @var string|null
     */
    public ?string $statusUrl = null;
    
    /**
     * URL to redirect the customer to if the payment is cancelled
     * 
     * @var string|null
     */
    public ?string $cancelUrl = null;
    
    /**
     * Validate notification are valid URLs
     * @return errors array List of validation errors, empty array for no errors
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->returnUrl)) {
            $errors[] = 'Return URL is required';
        } elseif (!filter_var($this->returnUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid return URL format';
        }
        
        if (empty($this->statusUrl)) {
            $errors[] = 'Status URL is required';
        } elseif (!filter_var($this->statusUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid status URL format';
        }
        
        //cancelURL is optional, but if provided, it should be a valid URL
        if (!empty($this->cancelUrl) && !filter_var($this->cancelUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid cancel URL format';
        }
        
        return $errors;
    }
}
