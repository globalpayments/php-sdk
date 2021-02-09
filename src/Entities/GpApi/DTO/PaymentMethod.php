<?php


namespace GlobalPayments\Api\Entities\GpApi\DTO;


class PaymentMethod
{
    public $id;
    public $entry_mode;
    /** @var $authentication Authentication */
    public $authentication;
    public $encryption;
    public $name;

    /** @var Card $card  */
    public $card;
}