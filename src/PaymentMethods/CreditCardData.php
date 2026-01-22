<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\Utils\CardUtils;

class CreditCardData extends Credit implements ICardData
{
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
     * Set the Card on File storage
     *
     * @var bool
     */
    public ?string $cardBrandTransactionId = null;

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

    /**
     * @return string
     */
    public function getShortExpiry()
    {
        if ($this->expMonth != null && $this->expYear != null) {
            return sprintf(
                '%s%s',
                str_pad($this->expMonth, 2, '0', STR_PAD_LEFT),
                substr(str_pad($this->expYear, 4, '0', STR_PAD_LEFT), 2, 2)
            );
        }
        return null;
    }

    /**
     * Gets a card's type based on the BIN
     *
     * @return string
     */
    public function getCardType()
    {
        if (empty($this->number)) {
            return;
        }
        return CardUtils::getCardType($this->number);
    }

    public function hasInAppPaymentData()
    {
        return !empty($this->token) && !empty($this->mobileType);
    }
}
