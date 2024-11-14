<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;

class RequestParamFields implements IRequestSubGroup
{
    /*
     * ID of the clerk if in retail mode, and ID of the server if in restaurant mode.
     */
    public $clerkId = null;
    
    /*
     * When enabled create token request is sent to Portico to generate a token for a cardholder
     * 
     * Possible values: 0 or 1
     */
    public $tokenRequest = null;
    
    /*
     * Token returned previously by the host.
     */
    public $tokenValue = null;

    /*
     *Card On File Indicator
     */
    public $cardOnFileIndicator = null;

    public $cardBrandTransId = null;

    public ?string $invoiceNbr = null;
    /** @var string|null Indicates the Direct Market Invoice number */
    public ?string $directMktInvoiceNbr = null;
    /** @var string|null Indicates the Direct Market Ship Month. */
    public ?string $directMktShipMonth = null;
    /** @var string|null Indicates the Direct Market Ship Day. */
    public ?string $directMktShipDay = null;

    public ?int $timeout;
    public ?string $acquisitionTypes;
    public ?string $displayTotalAmount;
    public ?string $PromptForManualEntryPassword;
    public ?string $merchantDecision;
    public ?string $languageCode;
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
        switch ($builder->transactionType) {
            case TransactionType::VOID:
                $this->clerkId = $builder->clerkId ?? null;
                return;
            case TransactionType::SALE:
            case TransactionType::REFUND:
            case TransactionType::VERIFY:
            case TransactionType::AUTH:
            case TransactionType::CAPTURE:
                $this->clerkId = $builder->clerkId;
                break;
            case TransactionType::EDIT:
                if ($builder->transactionModifier !== TransactionModifier::UPDATE_LODGING_DETAILS) {
                    $this->clerkId = $builder->clerkId;
                }
                break;
            default:
                break;
        }

        if (!empty($builder->cardOnFileIndicator)) {
            $this->cardOnFileIndicator = ($builder->cardOnFileIndicator === StoredCredentialInitiator::CARDHOLDER)
                                            ? 'C' : 'M';
        }

        if (!empty($builder->cardBrandTransId)) {
            $this->cardBrandTransId = $builder->cardBrandTransId;
        }
        
        if (!empty($builder->requestMultiUseToken)) {
            $this->tokenRequest = $builder->requestMultiUseToken === true ? 1 : 0;
        }
        
        if (
            $builder->paymentMethod instanceof CreditCardData &&
            !empty($builder->paymentMethod->token)
        ) {
            $this->tokenValue = $builder->paymentMethod->token;
        }

        if (isset($builder->shippingDate) && !empty($builder->invoiceNumber)) {
            $this->directMktInvoiceNbr = $builder->invoiceNumber;
            $this->directMktShipMonth = $builder->shippingDate->format('m');
            $this->directMktShipDay = $builder->shippingDate->format('d');
        }
        $this->timeout = $builder->timeout ?? null;
        if (!empty($builder->acquisitionTypes)) {
            $acquisitionTypes = '';
            array_walk($builder->acquisitionTypes, function ($v) use (&$acquisitionTypes) {
                $acquisitionTypes .= $v . '|';
            });
            $this->acquisitionTypes = rtrim($acquisitionTypes, '|');
        }
        if (!empty($builder->displayTotalAmount)) {
            $this->displayTotalAmount = $builder->displayTotalAmount === true ? 'Y' : 'N';
        }
        $this->PromptForManualEntryPassword = $builder->promptForManualEntryPassword ?? null;
        $this->merchantDecision = $builder->merchantDecision ?? null;
        $this->languageCode = $builder->language ?? null;
    }
}
