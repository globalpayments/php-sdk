<?php
//Entity class for hosted payment page order.payment_method_configuration data

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\{HPPAuthenticationConfiguration, HPPApmConfiguration};
use GlobalPayments\Api\Entities\Enums\{HPPDigitalWalletProvider, HPPStorageModes, PaymentEntryMode};

class HPPPaymentMethodConfiguration
{
    /**
     * Entry mode for payment method (e.g., ECOM, MOTO, etc.)
     * @var string|null
     */
    public ?string $entryMode = null;
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
        HPPStorageModes|string|null $storageMode = HPPStorageModes::PROMPT,
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
                $allowedStorageModes = implode(', ', array_values((new \ReflectionClass(HPPStorageModes::class))->getConstants()));
                $errors[] = 'Invalid storage mode: ' . $this->storageMode . '. Allowed values: ' . $allowedStorageModes;
            }
        }

        // Validate entry mode if provided
        if ($this->entryMode !== null) {
            $entryMode = trim((string)$this->entryMode);
            try {
                PaymentEntryMode::validate($entryMode);
            } catch (\Exception $e) {
                $allowedEntryModes = implode(', ', array_values((new \ReflectionClass(PaymentEntryMode::class))->getConstants()));
                $errors[] = 'Invalid entry mode: ' . $this->entryMode . '. Allowed values: ' . $allowedEntryModes;
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
            } else {
                $allowedProviders = array_values((new \ReflectionClass(HPPDigitalWalletProvider::class))->getConstants());
                foreach ($this->digitalWallets['provider'] as $provider) {
                    if (!is_scalar($provider) || is_bool($provider)) {
                        $errors[] = 'Invalid digital wallet provider type. Provider values must be strings.';
                        continue;
                    }
                    $providerValue = trim((string)$provider);
                    if ($providerValue === '' || !in_array($providerValue, $allowedProviders, true)) {
                        $errors[] = 'Invalid digital wallet provider: ' . $providerValue . '. Allowed values: ' . implode(', ', $allowedProviders);
                    }
                }
            }
        }
        
        return $errors;
    }
}
