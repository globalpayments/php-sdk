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
     * Request Id used by the POS to uniquely identify transactions.
     * Id is sent in the request and is then echoed back to the POS in the transaction response.
     *
     * @internal
     * @var int
     */
    public $requestId;
    
    /*
     * ID of the clerk if in retail mode, and ID of the server if in restaurant mode
     * 
     * @var int
     */
    public $clerkId;

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
    
    public function withClerkId($clerkId)
    {
        $this->clerkId = $clerkId;
        return $this;
    }
}
