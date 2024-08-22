<?php
namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\ExtraChargeType;

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

    /** @var array<ExtraChargeType> */
    public array $extraCharges;
    /** @var string Lodging system generated value used to group and manage charges during a stay */
    public string $folioNumber;

}
