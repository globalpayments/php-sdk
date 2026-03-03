<?php

namespace GlobalPayments\Api\Entities;
class InstallmentData
{
    /**
     * Installment ID (from installment query)
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $program;

    /**
     * Installment ID reference (from installment query)
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $mode;

    /**
     * Visa installment funding mode (e.g., 'MERCHANT', 'ISSUER')
     * @var string
     */
    public $funding_mode;

    /**
     * @var string
     */
    public $count;

    /**
     * @var string
     */
    public $grace_period_count;

    /**
     * Visa installment terms
     * @var InstallmentTerms
     */
    public ?InstallmentTerms $terms = null;

    /**
     * Array of eligible plans for Visa installments
     * @var array
     */
    public $eligible_plans;
}
