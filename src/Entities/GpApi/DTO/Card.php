<?php


namespace GlobalPayments\Api\Entities\GpApi\DTO;


class Card
{
    public $brand;
    public ?string $token_format = null;
    public $number;
    public $expiry_month;
    public $expiry_year;
    public $cvv;
    public ?string $cvv_present = null;
    public $cvv_indicator;
    public $avs_address;
    public $avs_postal_code;
    public $track;
    public $tag;
    public $funding;
    public $chip_condition;
    public $pin_block;
    public $brand_reference;
    public $authcode;

 }