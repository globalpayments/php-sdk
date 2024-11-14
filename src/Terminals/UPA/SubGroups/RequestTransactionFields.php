<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;

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

    /* The initial amount authorized on the original preauth transaction. */
    public $preAuthAmount = null;
    
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
    
    public $amount = null;
    
    public $referenceNumber = null;
    
    //Health Card related fields
    public $cardIsHSAFSA = null;
    
    public $prescriptionAmount = null;
    
    public $clinicAmount = null;
    
    public $dentalAmount = null;
    
    public $visionOpticalAmount = null;

    public ?int $processCPC = null;

    /** The new authorized amount of the transaction. This is required only when reversing a
    partially authorized transaction. If not specified, the full original amount will be reversed. */
    public ?string $authorizedAmount;

    /**
     * Indicates the mode of card reading
     * @var string|null
     */
    public ?string $cardAcquisition;

    public ?int $allowDuplicate;
    /** @var int HSA/FSA Token Transaction */
    public int $HSAFSATokenTran;

    /** @var string Purchase Order to be sent to the host */
    public ?string $purchaseOrder;

    /** @var int|null ID of the clerk if in retail mode and lodging, and ID of the server if in restaurant mode */
    public ?int $clerkId;
    /** @var string If not supplied, it will use the default of PA. If supplied, will use this parameter instead.  */
    public string $confirmAmount;
    /** @var string Indicates the type of transaction */
    public string $transactionType;
    public string $tranDate;
    public string $tranTime;

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
    
    public function setParams(TerminalBuilder $builder)
    {
        if (isset($builder->amount)) {
            switch ($builder->transactionType)
            {
                case TransactionType::REFUND:
                    $this->totalAmount = sprintf('%08.2f', $builder->amount);
                    break;
                case TransactionType::AUTH:
                case TransactionType::CAPTURE:
                    $this->amount = sprintf('%08.2f', $builder->amount);
                    break;
                case TransactionType::DELETE:
                    if ($builder->transactionModifier == TransactionModifier::DELETE_PRE_AUTH) {
                        $this->preAuthAmount = sprintf('%01.2f', $builder->amount);
                    }
                    break;
                case TransactionType::REVERSAL:
                    $this->authorizedAmount = sprintf('%01.2f', $builder->amount);
                    break;
                case TransactionType::EDIT:
                    if ($builder->transactionModifier == TransactionModifier::UPDATE_LODGING_DETAILS) {
                        $this->amount = sprintf('%07.2f', $builder->amount);
                        $this->clerkId = $builder->clerkId ?? null;
                    }
                    break;
                case TransactionType::SALE:
                    if (
                        $builder->transactionModifier == TransactionModifier::START_TRANSACTION ||
                        $builder->transactionModifier == TransactionModifier::PROCESS_TRANSACTION
                    ) {
                        $this->totalAmount = sprintf('%08.2f', $builder->amount);
                    } else {
                        $this->baseAmount = sprintf('%07.2f', $builder->amount);
                    }
                    break;
                case TransactionType::CONFIRM:
                    if (
                        $builder->transactionModifier == TransactionModifier::CONTINUE_EMV_TRANSACTION ||
                        $builder->transactionModifier == TransactionModifier::CONTINUE_CARD_TRANSACTION
                    ) {
                        $this->totalAmount = sprintf('%08.2f', $builder->amount);
                        break;
                    }
                    break;
                case TransactionType::VOID:
                    $this->tranNo = $builder->terminalRefNumber ?? null;
                    $this->referenceNumber = $builder->paymentMethod->transactionId ?? null;
                    return;
                default:
                    $this->baseAmount = sprintf('%07.2f', $builder->amount);
                    break;
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

        if ($builder->paymentMethod != null &&
            $builder->paymentMethod instanceof TransactionReference &&
            !empty($builder->paymentMethod->transactionId)
        ) {
            $this->referenceNumber = $builder->paymentMethod->transactionId;
        }
        
        if (!empty($builder->autoSubstantiation)) {
            $this->setHealthCardData($builder->autoSubstantiation);
        }

        $this->taxIndicator = $builder->taxExempt ?? null;
        $this->processCPC = $builder->processCPC ?? null;
        $this->purchaseOrder = $builder->orderId ?? null;
        if (isset($builder->confirmAmount)) {
            $this->confirmAmount = $builder->confirmAmount === true ? "Y" : "N";
        }
        $this->allowDuplicate = $builder->allowDuplicates ?? null;
        /** parameter set only for command StartCardTransaction */
        if (
            $builder->transactionModifier == TransactionModifier::START_TRANSACTION ||
            $builder->transactionModifier == TransactionModifier::PROCESS_TRANSACTION
        ) {
            $this->transactionType = isset($builder->transactionType) ?
                ucfirst(strtolower(TransactionType::getKey($builder->transactionType))) : null;
        }
        if (isset($builder->transactionDate)) {
            $this->tranDate = $builder->transactionDate->format('mdY');
            $this->tranTime = $builder->transactionDate->format('H:i:s');
        }
    }
    
    private function setHealthCardData($autoSubstantiation)
    {
        if (!empty($autoSubstantiation->getPrescriptionSubTotal())) {
            $this->prescriptionAmount = sprintf('%06.2f', $autoSubstantiation->getPrescriptionSubTotal());
        }
        
        if (!empty($autoSubstantiation->getClinicSubTotal())) {
            $this->clinicAmount = sprintf('%06.2f', $autoSubstantiation->getClinicSubTotal());
        }
        
        if (!empty($autoSubstantiation->getDentalSubTotal())) {
            $this->dentalAmount = sprintf('%06.2f', $autoSubstantiation->getDentalSubTotal());
        }
        
        if (!empty($autoSubstantiation->getVisionSubTotal())) {
            $this->visionOpticalAmount = sprintf('%06.2f', $autoSubstantiation->getVisionSubTotal());
        }
        
        if ($this->prescriptionAmount > 0 || $this->clinicAmount > 0 || $this->dentalAmount > 0
            || $this->visionOpticalAmount > 0) {
            $this->cardIsHSAFSA = 1;
        }
    }
}
