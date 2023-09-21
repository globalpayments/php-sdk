<?php

namespace GlobalPayments\Api\Terminals\Genius\Builders;

use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;

class MitcManageBuilder extends TerminalManageBuilder
{
    /**
     * 
     * @var TransactionType
     */
    public $followOnTransactionType;

    /**
     * 
     * @param TransactionType $transactionType 
     * @param mixed $paymentMethodType 
     * @param TransactionType $followOnTransactionType 
     * @return void 
     */
    public function __construct(
        $transactionType,
        $followOnTransactionType,
        $paymentMethodType = null
    )
    {
        parent::__construct($transactionType, $paymentMethodType);

        $this->followOnTransactionType = $followOnTransactionType;
    }
}
