<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Builders\TransactionBuilder;

abstract class TerminalBuilder extends TransactionBuilder
{

    /**
     * Request transaction type
     *
     * @internal
     * @var PaymentMethodType
     */
    public $paymentMethodType;

    /**
     * Request transaction Id
     *
     * @internal
     * @var int
     */
    public $requestId;

    public function __construct($type, $paymentMethodType)
    {
        $this->paymentMethodType = $paymentMethodType;
        parent::__construct($type);
    }
    
    public function withRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }
}
