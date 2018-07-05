<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;

class CreditCardData extends Credit implements ICardData
{
    /**
     * Card number
     *
     * @var string
     */
    public $number;

    /**
     * Card expiration month
     *
     * @var string
     */
    public $expMonth;

    /**
     * Card expiration year
     *
     * @var string|float
     */
    public $expYear;

    /**
     * Card verification number
     *
     * @var string|float
     */
    public $cvn;

    /**
     * CVN presence indicator
     *
     * @var CvnPresenceIndicator
     */
    public $cvnPresenceIndicator;

    /**
     * Card holder name
     *
     * @var string
     */
    public $cardHolderName;

    /**
     * Card present
     *
     * @var bool
     */
    public $cardPresent;

    /**
     * Card reader present
     *
     * @var bool
     */
    public $readerPresent;

    /**
     * Card type regex patterns
     *
     * @var array
     */
    public static $cardTypes = [
        'Visa' => '/^4/',
        'MC' => '/^(5[1-5]|2[2-7])/',
        'Amex' => '/^3[47]/',
        'DinersClub' => '/^3[0689]/',
        'EnRoute' => '/^2(014|149)/',
        'Discover' => '/^6([045]|22)/',
        'Jcb' => '/^35/',
    ];

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
        return sprintf(
            '%s%s',
            str_pad($this->expMonth, 2, '0', STR_PAD_LEFT),
            substr(str_pad($this->expYear, 4, '0', STR_PAD_LEFT), 2, 2)
        );
    }

    /**
     * Gets a card's type based on the BIN
     *
     * @return string
     */
    public function getCardType()
    {
        $number = str_replace(
            [' ', '-'],
            '',
            $this->number
        );

        foreach (static::$cardTypes as $type => $regex) {
            if (1 === preg_match($regex, $this->number)) {
                return $type;
            }
        }

        return 'Unknown';
    }
}
