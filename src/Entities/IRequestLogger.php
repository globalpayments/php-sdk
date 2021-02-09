<?php


namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Gateways\GatewayResponse;

interface IRequestLogger
{
    public function requestSent($verb, $endpoint, $headers, $queryStringParams, $data);

    public function responseReceived(GatewayResponse $response);

    public function responseError(\Exception $e);
}