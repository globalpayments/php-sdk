<?php

namespace GlobalPayments\Api\Entities;
class Installment
{
    /**
     * @var string
     */
    public $program;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var string
     */
    public $count;

    /**
     * @var string
     */
    public $grace_period_count;
}
