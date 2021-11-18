<?php

namespace GlobalPayments\Api\Terminals\UPA\Builders;

use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;

class UpaTerminalManageBuilder extends TerminalManageBuilder
{
    /**
     * {@inheritdoc}
     *
     * @param TransactionType $transactionType Request transaction type
     * @param PaymentMethodType $paymentMethodType Request payment method
     *
     * @return
     */
    public function __construct($transactionType, $paymentMethodType = null)
    {
        parent::__construct($transactionType, $paymentMethodType);
    }

    protected function setupValidations()
    {
        $this->validations->of(
            TransactionType::CAPTURE
        )
                ->with(TransactionModifier::NONE)
                ->check('amount')->isNotNull()
                ->check('terminalRefNumber')->isNotNull();
        
        $this->validations->of(
            TransactionType::VOID
        )
                ->with(TransactionModifier::NONE)
                ->check('terminalRefNumber')->isNotNull();
    }
}
