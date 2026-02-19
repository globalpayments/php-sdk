<?php

namespace GlobalPayments\Api\Entities;

/**
 * Configuration class for installment terms
 * goes in installment.terms
 */
class InstallmentsTerms
{
    /**
     * Maximum time unit number for installments example "24" for 24 months
     * @var string
     */
    public $maxTimeUnitNumber;

    /**
     * Threshold amount for displyed installments
     * @var string
     */
    public $maxAmount;

    /**
     * Constructor for InstallmentsTerms
     * 
     * @param string $maxTimeUnitNumber The maximum number of installment periods
     * @param string $maxAmount The threshold amount in minor units
     */
    public function __construct($maxTimeUnitNumber = null, $maxAmount = null)
    {
        $this->maxTimeUnitNumber = $maxTimeUnitNumber;
        $this->maxAmount = $maxAmount;
    }

    /**
     * Validate the installments terms
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];

        // Validate max time unit number
        if (!empty($this->maxTimeUnitNumber)) {
            if (!is_numeric($this->maxTimeUnitNumber) || (int)$this->maxTimeUnitNumber <= 0) {
                $errors[] = 'Max time unit number must be a positive integer';
            }
        }

        // Validate max amount
        if (!empty($this->maxAmount)) {
            if (!is_numeric($this->maxAmount) || (int)$this->maxAmount <= 0) {
                $errors[] = 'Max amount must be a positive integer in minor units';
            }
        }

        return $errors;
    }
}