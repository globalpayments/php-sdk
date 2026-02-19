<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\InstallmentsFundingMode;

/**
 * Configuration class for installments filtering options
 */
class InstallmentsConfiguration
{
    /**
     * The funding mode for installments filtering
     * @var string
     */
    public $fundingMode;

    /**
     * Terms configuration for installments filtering
     * @var InstallmentsTerms|Object|null
     */
    public $terms;

    /**
     * Constructor for InstallmentsConfiguration
     * @param InstallmentsFundingMode|string $fundingMode The funding mode example: MERCHANT_FUNDED
     * @param InstallmentsTerms|Object $terms The installment terms configuration
     */
    public function __construct(?string $fundingMode = null, ?InstallmentsTerms $terms = null)
    {
        $this->fundingMode = $fundingMode;
        $this->terms = $terms ?? new InstallmentsTerms();
    }

    /**
     * Validate the installments data
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];

        // Validate funding mode
        if (empty($this->fundingMode)) {
            $errors[] = 'Installments funding mode is required';
        }

        // Validate terms if present
        if ($this->terms) {
            $termsErrors = $this->terms->validate();
            $errors = array_merge($errors, $termsErrors);
        }

        return $errors;
    }
}