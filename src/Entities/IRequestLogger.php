<?php


namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Gateways\GatewayResponse;

interface IRequestLogger
{
    public function requestSent(string $verb, string $endpoint, array $headers, $queryStringParams, $data): void;

    public function responseReceived(GatewayResponse $response): void;

    public function responseError(\Exception $e, mixed $headers = ''): void;
}