<?php
//Entity class for hosted payment page order.payment_method_configuration data

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\{HPPAuthenticationConfiguration, HPPApmConfiguration};
use GlobalPayments\Api\Entities\Enums\HPPStorageModes;

class HPPPaymentMethodConfiguration
{
    /**
     * Provides authentication data, this will include 3DS challenge preference, 
     * @var HPPAuthenticationConfiguration|null
     */
    public ?HPPAuthenticationConfiguration $authentications = null;
    /**
     * This is PayPal specific data
     * @var HPPApmConfiguration|null
     */
    public ?HPPApmConfiguration $apm = null;
    /**  Storage mode for the payment method, determines if the users card data should be saved in the Globalpay API
     * 
     * @var HPPStorageModes|null
     */
    public ?string $storageMode = null;
    /**
     * Digital wallets configuration for the payment method
     * @var array|null
     */
    public ?array $digitalWallets = null;

    public function __construct(
        HPPAuthenticationConfiguration $config = new HPPAuthenticationConfiguration(),
        HPPApmConfiguration $apm = new HPPApmConfiguration(),
        HPPStorageModes|string $storageMode = HPPStorageModes::PROMPT,
        ?array $digitalWallets = null
    )
    {
        $this->authentications = $config;
        $this->apm = $apm;
        $this->storageMode = HPPStorageModes::validate($storageMode);
        $this->digitalWallets = $digitalWallets;
    }

    /**
     * Validate the HPPPaymentMethodConfiguration
     * @return errors Array of validation errors, empty array if valid
     */

    public function validate(): array
    {
        $errors = [];
        
        if ($this->authentications) {
            $errors = array_merge($errors, $this->authentications->validate());
        }
        
        if ($this->apm) {
            $errors = array_merge($errors, $this->apm->validate());
        }
        
        // Use the Enum::validate method to validate storageMode property if provided
        if ($this->storageMode !== null) {
            try {
                HPPStorageModes::validate($this->storageMode);
            } catch (\Exception $e) {
                $errors[] = 'Invalid storage mode: ' . $this->storageMode;
            }
        }

        // Validate digitalWallets if set
        if ($this->digitalWallets !== null) {
            if (
                !is_array($this->digitalWallets) ||
                !isset($this->digitalWallets['provider']) ||
                !is_array($this->digitalWallets['provider'])
            ) {
                $errors[] = 'digital_wallets must be an array with a "provider" key containing an array of strings.';
            }
        }
        
        return $errors;
    }
}
