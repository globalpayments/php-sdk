<?php

namespace GlobalPayments\Api\Entities;

class AdditionalTaxDetails
{

    /**
     * Tax amount
     *
     * @var string|float
     */
    public $taxAmount;

    /**
     * The type of tax.
     *
     * @var TaxCategory
     */
    public $taxCategory;

    /**
     * @var float
     */
    public $taxRate;

    /**
     * The type of tax. For example, VAT, NATIONAL, SALESTAX
     *
     * @var TaxType|string
     */
    public $taxType;

    public function __construct($taxAmount = null, $taxCategory = null, $taxRate = null, $taxType = null)
    {
        $this->taxAmount = $taxAmount;
        $this->taxCategory = $taxCategory;
        $this->taxRate = $taxRate;
        $this->taxType = $taxType;
    }
}
