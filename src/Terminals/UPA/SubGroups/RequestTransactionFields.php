<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Terminals\Interfaces\IRequestSubGroup;

class RequestTransactionFields implements IRequestSubGroup
{
    /*
     * The initial amount before adding any additional amount like tax, tips etc.
     */
    public $baseAmount = null;
    
    /*
     * The amount that merchants charge for tax processing. If this is included as an
     * input parameter, tax amount will not be prompted during the Sale transaction.
     */
    public $taxAmount = null;
    
    /*
     * Tip amount for the transaction. If this is included as an input parameter, tip amount will not be prompted 
     * during the Sale transaction.
     */
    public $tipAmount = null;
    
    /*
     * Indicates whether the sale is exempted from Tax or not. Possible Values:0 or 1
     * 
     * If this is included as an input parameter, the tax exempt screen will not be prompted 
     * during the Sale transaction.
     */
    public $taxIndicator = null;
    
    /*
     * Cash back amount for PIN Debit Transactions. If this is included as an input
     * parameter, cash back amount will not be prompted during the Sale transaction.
     */
    public $cashBackAmount = null;
    
    /*
     * Indicates the Invoice number. If this is included as an input parameter, invoice number 
     * will not be prompted during the Sale transaction.
     */
    public $invoiceNbr = null;
    
    /*
     * The reference number of the transaction to be voided.
     */
    public $tranNo = null;
    
    public $totalAmount = null;
        
    /*
     * return Array
     */
    public function getElementString()
    {
        // Strip null values
        return array_filter((array) $this, function ($val) {
            return !is_null($val);
        });
    }
    
    public function setParams($builder)
    {
        if (isset($builder->amount)) {
            if ($builder->transactionType == TransactionType::REFUND) {
                $this->totalAmount = sprintf('%08.2f', $builder->amount);
            } else {
                $this->baseAmount = sprintf('%07.2f', $builder->amount);
            }
        }
        
        if (isset($builder->gratuity)) {
            $this->tipAmount = sprintf('%06.2f', $builder->gratuity);
        }
        
        if (isset($builder->cashBackAmount)) {
            $this->cashBackAmount = sprintf('%06.2f', $builder->cashBackAmount);
        }
        
        if (isset($builder->taxAmount)) {
            $this->taxAmount = sprintf('%06.2f', $builder->taxAmount);
        }
        
        if (!empty($builder->invoiceNumber)) {
            $this->invoiceNbr = $builder->invoiceNumber;
        }
        
        if (!empty($builder->terminalRefNumber)) {
            $this->tranNo = $builder->terminalRefNumber;
        }
    }
}
