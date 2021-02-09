<?php

namespace GlobalPayments\Api\Gateways;

class GatewayResponse
{
    /**
     * @var integer
     */
    public $statusCode;

    /**
     * @var string
     */
    public $rawResponse;

    /**
     * @var string
     */
    public $header;
}
