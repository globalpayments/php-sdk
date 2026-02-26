<?php

namespace GlobalPayments\Api\Entities;

/**
 * Visa installment terms configuration
 */
class InstallmentTerms
{
    /**
     * Time unit for installment (e.g., 'MONTH', 'DAY')
     * @var string
     */
    public ?string $time_unit = null;

    /**
     * Maximum number of time units allowed
     * @var int
     */
    public ?int $max_time_unit_number = null;

    /**
     * Maximum amount per installment
     * @var string|float
     */
    public string|float $max_amount;

    /**
     * Language code for Terms & Conditions (for transactions)
     * @var string
     */
    public ?string $language = null;

    /**
     * Version of Terms & Conditions accepted (for transactions)
     * @var string
     */
    public ?string $version = null;
}
