<?php

namespace GlobalPayments\Api\Gateways;

/**
 * Allows clients to implement an object capable of capturing raw API messages for
 * tracing/auditing purposes
 *
 * NOTE: clients are responsible for ensuring captured messages are handled securely, including
 * ensuring that raw cardholder data and other sensitive data is appropriately redacted
 */
interface ICommsTracer
{
    /**
     * @var string $string
     */
    public function captureTrace($string);
}
