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
    public mixed $paymentMethodType = null;

    /**
     * Request Id used by the POS to uniquely identify transactions.
     * Id is sent in the request and is then echoed back to the POS in the transaction response.
     *
     * @internal
     * @var int
     */
    public ?int $requestId = null;

    /*
     * ID of the clerk if in retail mode, and ID of the server if in restaurant mode
     * 
     * @var int
     */
    public ?string $clerkId = null;

    /*
     * Card On File Indicator, C - Cardholder initiated transaction, M - Merchant initiated transaction
     *
     * @var int
     */
    public ?string $cardOnFileIndicator = null;

    /*
     * Network Transaction Identifier
     *
     * @var int
     */
    public ?string $cardBrandTransId = null;

    public ?string $ecrId = null;

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

    public function withCardOnFileIndicator($cardOnFileIndicator)
    {
        $this->cardOnFileIndicator = $cardOnFileIndicator;
        return $this;
    }

    public function withCardBrandTransId($cardBrandTransId)
    {
        $this->cardBrandTransId = $cardBrandTransId;
        return $this;
    }

    public function withPaymentMethodType($paymentMethodType)
    {
        $this->paymentMethodType = $paymentMethodType;
        return $this;
    }
}
