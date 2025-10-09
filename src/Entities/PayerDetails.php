<?php

namespace GlobalPayments\Api\Entities;

class PayerDetails
{
    public ?string $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?Address $billingAddress = null;
    public ?Address $shippingAddress = null;
    public ?string $name = null;
    public ?string $status = null;
    public ?string $language = null;
    public ?string $addressMatchIndicator = null;
    public ?PhoneNumber $mobilePhone = null;
    public ?PhoneNumber $shippingPhone = null;

    /**
     * Validate the payer details
     * 
     * @return array Array of validation errors, return empty array if no errors found
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields validation
        if (empty($this->email)) {
            $errors[] = 'Payer email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Payer email must be a valid email address';
        }

        // Status validation
        if (!empty($this->status)) {
            if (!in_array($this->status, ['NEW', 'ACTIVE'])) {
                $errors[] = 'Payer status must be either "NEW" or "ACTIVE"';
            }
            
            // If status is ACTIVE, id field is required
            if ($this->status === 'ACTIVE' && empty($this->id)) {
                $errors[] = 'Payer id is required when status is "ACTIVE"';
            }
            
            // If status is NEW, id should not be provided
            if ($this->status === 'NEW' && !empty($this->id)) {
                $errors[] = 'Payer id should not be provided when status is "NEW"';
            }
        }

        // Name validation
        if (empty($this->firstName) && empty($this->lastName) && empty($this->name)) {
            $errors[] = 'At least one of firstName, lastName, or name is required for payer';
        }

        return $errors;
    }
}