<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\{HPPTypes, HPPFunctions};

/**
 * Hosted Payment Page request data entity for the request structure, this is the body of the request
 */
class HPPData
{
    /**
     * Type of HPP request, default is HPPTypes::HOSTED_PAYMENT_PAGE
     * @var HPPTypes
     */
    public $type = HPPTypes::HOSTED_PAYMENT_PAGE;

    /**
     * Name of the hosted payment page, will be shown on realcontrol
     * @var string
     */
    public $name;

    /**
     * Description of the payment, will also be shown on realcontrol
     * @var string
     */
    public $description;

    /**
     * Reference for the payment
     * @var string
     */
    public $reference;

    /**
     * Expiration date of the payment page
     * @var string
     */
    public $expirationDate;

    /**
     * Payer details, consiting of payer information/addresses/phone numbers, but also used for active payer
     * @var PayerDetails
     */
    public $payer;

    /**
     * Order information including amount, currency,transaction configuration, currncy_conversion_mode and 
     * allowed payment methods
     * @var HPPOrder
     */
    public $order;

    /**
     * Callback notifications URLs
     * @var HPPNotifications
     */
    public $notifications;

    /**
     * Add an Image hosted payment page - Not currently working
     * @var array
     */
    public $images;

    /**
     * Indicates if the shipping is chargeable, if set to true, shipping_amount must be provided
     * @var bool
     */
    public $shippable = false;

    /**
     * Additional shipping fee when shippable is set to YES
     * @var string|null String representation of shipping amount
     */
    public $shippingAmount = null;
    
    /**
     * Function of the hosted payment page
     * @var HPPFunctions enum value
     */
    public $function;
    
    /**
     * Display configuration for iframe callbacks
     * @var array Configuration array with iframe settings
     */
    public $HPPDisplayConfiguration;
    
    /**
     * Referrer URL for the hosted payment page
     * @var string URL of the refer page
     */
    public $referrerUrl;
    
    /**
     * IP address information for non HOSTED_PAYMENT_PAGE types
     * @var string IP address of the page hosting the third-party page
     */
    public $ipAddress;
    
    /**
     * IP subnet mask information for non HOSTED_PAYMENT_PAGE types
     * @var string|null Optional IP subnet mask
     */
    public $ipSubnetMask;
    
    /**
     * Email where app credentials should be sent, not used in hosted payment pages
     * @var string|null Email address for app credentials
     */
    public $appEmail;
    
    /**
     * App IDs for credential exchange functionality, not used in hosted payment pages
     * @var array|null Array of app IDs for EXCHANGE_APP_CREDENTIALS type
     */
    public $appIds;

    /**
     * Validate the hosted payment page data
     * @return errors array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields validation
        if (empty($this->name)) {
            $errors[] = 'Name is required for hosted payment page';
        }

        if (empty($this->type)) {
            $errors[] = 'Type is required for hosted payment page';
        }

        // Validate shippable property (should be boolean)
        if (!is_bool($this->shippable)) {
            $errors[] = 'Shippable must be a boolean value';
        }

        // Validate shipping_amount when shippable is true
        if ($this->shippable === true && !empty($this->shippingAmount)) {
            if (!is_numeric($this->shippingAmount) || (float)$this->shippingAmount < 0) {
                $errors[] = 'Shipping amount must be a valid positive number when shippable is true';
            }
        }
        
        // Validate type if set
        if (!empty($this->type)) {
            try {
                HPPTypes::validate($this->type);
            } catch (\Exception $e) {
                $errors[] = 'Invalid hosted payment page type';
            }
        }
        
        // Validate function if set
        if (!empty($this->function)) {
            try {
                HPPFunctions::validate($this->function);
            } catch (\Exception $e) {
                $errors[] = 'Invalid hosted payment function';
            }
        }

        // Payer validation
        if (!$this->payer) {
            $errors[] = 'Payer details are required';
        } else {
            $payerErrors = $this->payer->validate();
            $errors = array_merge($errors, $payerErrors);
        }

        // Order validation
        if (!$this->order) {
            $errors[] = 'Order details are required';
        } else {
            $orderErrors = $this->order->validate();
            $errors = array_merge($errors, $orderErrors);
        }

        // Notifications validation
        if (!$this->notifications) {
            $errors[] = 'Notifications configuration is required';
        } else {
            $notificationErrors = $this->notifications->validate();
            $errors = array_merge($errors, $notificationErrors);
        }

        return $errors;
    }

}
