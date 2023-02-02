<?php

namespace GlobalPayments\Api\Entities;

class ThirdPartyResponse
{
    /**
     * @var string
     */
    public $platform;

    /**
     * Data json string that represents the raw data received from another platform.
     * @var string
     */
    public $data;
}