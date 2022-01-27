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
    public $storage_mode;
    /** @var Card $card  */
    public $card;
    public $digital_wallet;
    public $bank_transfer;
    /** @var array */
    public $apm;
    public $fingerprint_mode;

    const PAYMENT_METHOD_TOKEN_PREFIX = 'PMT_';
}