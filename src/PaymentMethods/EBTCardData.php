<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;

class EBTCardData extends EBT implements ICardData
{
    /**
     * Approval code
     *
     * @var string
     */
    public ?string $approvalCode = null;

    /**
     * Card number
     *
     * @var string
     */
    public ?string $number = null;

    /**
     * Card expiration month
     *
     * @var string
     */
    public string|int|null $expMonth = null;

    /**
     * Card expiration year
     *
     * @var string|float
     */
    public string|int|null $expYear = null;

    /**
     * Card verification number
     *
     * @var string|float
     */
    public string|int|null $cvn = null;

    /**
     * CVN presence indicator
     *
     * @var CvnPresenceIndicator
     */
    public mixed $cvnPresenceIndicator = null;

    /**
     * Card holder name
     *
     * @var string
     */
    public ?string $cardHolderName = null;

    /**
     * Card present
     *
     * @var bool
     */
    public ?bool $cardPresent = null;

    /**
     * Card reader present
     *
     * @var bool
     */
    public ?bool $readerPresent = null;

    /**
     * Card serial number
     *
     * @var string
     */
    public ?string $serialNumber = null;

    /**
     * Instantiates a new credit card
     *
     * @return
     */
    public function __construct()
    {
        $this->cardPresent = false;
        $this->readerPresent = false;
        $this->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;
    }
}
