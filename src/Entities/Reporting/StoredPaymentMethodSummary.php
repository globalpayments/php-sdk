<?php


namespace GlobalPayments\Api\Entities\Reporting;


class StoredPaymentMethodSummary
{
    /**
     * @var string
     */
    public $paymentMethodId;

    /**
     * @var \DateTime
     */
    public $timeCreated;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $cardHolderName;

    /**
     * @var string
     */
    public $cardType;

    /**
     * @var string
     */
    public $cardNumberLastFour;

    /**
     * @var int
     */
    public $cardExpMonth;

    /**
     * @var int
     */
    public $cardExpYear;

}