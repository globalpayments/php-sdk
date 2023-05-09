<?php

namespace GlobalPayments\Api\Entities;

class FundsData
{
    /**
     * @var string
     */
    public $merchantId;

    /**
     * The merchant's account id that will be receiving the transfer
     * @var string
     */
    public $recipientAccountId;
}