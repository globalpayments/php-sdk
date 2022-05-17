<?php
namespace GlobalPayments\Api\Entities;

class LodgingData
{
    /**
     * @var string
     */
    public $prestigiousPropertyLimit;

    /**
     * @var bool
     */
    public $noShow;

    /**
     * @var string
     */
    public $advancedDepositType;

    /**
     * @var string
     */
    public $lodgingDataEdit;

    /**
     * @var bool
     */
    public $preferredCustomer;

    /** @var string */
    public $bookingReference;

    /** @var integer */
    public $durationDays;

    /** @var string */
    public $checkedInDate;

    /** @var string */
    public $checkedOutDate;

    /** @var string */
    public $dailyRateAmount;

    /** @var array<LodgingItems> */
    public $items;
}
