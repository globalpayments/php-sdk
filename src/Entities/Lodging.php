<?php

namespace GlobalPayments\Api\Entities;

class Lodging
{
    /** @var string */
    public $bookingReference;

    /** @var integer */
    public $durationDays;

    /** @var string */
    public $dateCheckedIn;

    /** @var string */
    public $dateCheckedOut;

    /** @var string */
    public $dailyRateAmount;

    /** @var array<LodgingItems> */
    public $items;
}