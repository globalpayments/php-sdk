<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\EcommerceChannel;

/**
 * Ecommerce specific data to pass during authorization/settlement.
 */
class EcommerceInfo
{
    /**
     * Consumer authentication (3DSecure) verification value.
     *
     * @var string
     */
    public $cavv;

    /**
     * Identifies eCommerce vs mail order / telephone order (MOTO) transactions.
     *
     * Default value is `EcommerceChannel.ECOM`.
     *
     * @var EcommerceChannel
     */
    public $channel;

    /**
     * Consumer authentication (3DSecure) electronic commerce indicator.
     *
     * @var string
     */
    public $eci;

    /**
     * Consumer authentication (3DSecure) source.
     *
     * @var string
     */
    public $paymentDataSource;

    /**
     * Consumer authentication (3DSecure) type.
     *
     * Default value is `"3DSecure"`.
     *
     * @var string
     */
    public $paymentDataType;

    /**
     * The expected shipping month.
     *
     * Default value is the date of one day in the future.
     *
     * @var integer
     */
    public $shipDay;

    /**
     * The expected shipping month.
     *
     * Default value is the month of one day in the future.
     *
     * @var integer
     */
    public $shipMonth;

    /**
     * Consumer authentication (3DSecure) transaction ID.
     *
     * @var string
     */
    public $xid;

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
        $this->paymentDataType = '3DSecure';
    }
}
