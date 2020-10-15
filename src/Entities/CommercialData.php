<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\CommercialIndicator;

/**
 * To contain info used in level-2 and level-3 transactions
 */
class CommercialData
{

    public function __construct($taxType, $commercialIndicator = CommercialIndicator::LEVEL_II)
    {
        $this->taxType = $taxType;
        $this->commercialIndicator = $commercialIndicator;
        $this->lineItems = array();
    }

    /**
     * can be used to send additional tax details for Level 2 and Level 3 transactions
     *
     * @var AdditionalTaxDetails
     */
    public $additionalTaxDetails;

    /**
     * to indicate LEVEL2 or LEVEL3
     *
     * @var CommercialIndicator
     */
    public $commercialIndicator;

    /**
     * The reference identifier supplied by the Commercial Card cardholder
     *
     * @var string
     */
    public $customerReferenceId;

    /**
     * Indicates the customer's government assigned tax identification number
     *
     * @var string
     */
    public $customerVatNumber;

    /**
     * The value of the Transaction Advice Addendum field, displays descriptive information about a transactions on a customer's AMEX card statement.
     *
     * @var string
     */
    public $description; // needed for AMEX w/TransIT

    /**
     *
     * @var string
     */
    public $destinationCountryCode;

    /**
     *
     * @var string
     */
    public $destinationPostalCode;

    public $discountAmount; // needed w/Genius

    /**
     *
     * @var float
     */
    public $dutyAmount; // needed for Visa w/TransIT

    /**
     *
     * @var float
     */
    public $freightAmount; // optional w/TransIT

    public $lineItems = array();

    /**
     * MM/DD/YYYY
     *
     * @var string
     */
    public $orderDate;

    /**
     *
     * @var string
     */
    public $originPostalCode;

    /**
     * Used by the customer to identify an order. Issued by the buyer.
     *
     * @var string
     */
    public $poNumber;

    /**
     * This field contains a reference number that is used by American Express to obtain supporting information on a charge from a merchant.
     *
     * @var string
     */
    public $supplierReferenceNumber;

    /**
     * The international description code of the overall goods or services being supplied.
     *
     * @var string
     */
    public $summaryCommodityCode;

    /**
     * Tax amount; typically sales tax
     *
     * @var string|float
     */
    public $taxAmount;

    /**
     * The type of tax. For example, VAT, NATIONAL, SALESTAX.
     *
     * @var TaxType|string
     */
    public $taxType;

    /**
     * The Value Added Tax (VAT) invoice number associated with the transaction.
     *
     * @var string
     */
    public $vatInvoiceNumber;

    /**
     *
     * @var CommercialLineItem
     */
    public function addLineItems()
    {
        foreach (func_get_args() as $lineItem) {
            array_push($this->lineItems, $lineItem);
        }
    }
}
