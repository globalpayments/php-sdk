<?php


namespace GlobalPayments\Api\Entities;


class Dispute extends Transaction
{
    public $reason;

    public $currency;

    public $reasonCode;

    public $stage;

    public $result;

    public $documents;
}