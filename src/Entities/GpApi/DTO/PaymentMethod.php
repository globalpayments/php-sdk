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
    public $storage_model;

    /** @var Card $card  */
    public $card;

    const PAYMENT_METHOD_TOKEN_PREFIX = 'PMT_';
}