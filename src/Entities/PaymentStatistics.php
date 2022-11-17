<?php

namespace GlobalPayments\Api\Entities;

class PaymentStatistics
{
    /**
     * The total monthly sales of the merchant.
     * @var float
     */
    public $totalMonthlySalesAmount;

    /**
     * The total monthly sales of the merchant.
     *
     * @var float
     */
    public $averageTicketSalesAmount;

    /**
     * The merchants highest ticket amount.
     *
     * @var float
     */
    public $highestTicketSalesAmount;
}