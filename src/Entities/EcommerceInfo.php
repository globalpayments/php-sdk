<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\EcommerceChannel;

/**
 * Ecommerce specific data to pass during authorization/settlement.
 */
class EcommerceInfo
{
    /**
     * Identifies eCommerce vs mail order / telephone order (MOTO) transactions.
     * Default value is `EcommerceChannel.ECOM`.
     * @var EcommerceChannel
     */
    public $channel;

    /**
     * The expected shipping month.
     * Default value is the date of one day in the future.
     */
    public int|string $shipDay;

    /**
     * The expected shipping month.
     * Default value is the month of one day in the future.
     */
    public int|string $shipMonth;

    /**
     * Instantiates a new `EcommerceInfo` object.
     *
     * @return
     */
    public function __construct()
    {
        $this->channel = EcommerceChannel::ECOM;
        $this->shipDay = (new \DateTime())
            ->add(new \DateInterval('P1D'))
            ->format('d');
        $this->shipMonth = (new \DateTime())
            ->add(new \DateInterval('P1D'))
            ->format('m');
    }
}
