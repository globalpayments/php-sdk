<?php

namespace GlobalPayments\Api\Terminals;

abstract class DeviceController
{
    public $deviceInterface;
    public $requestIdProvider;

    abstract public function send($message, $requestType = null);

    abstract public function processTransaction($builder);

    abstract public function manageTransaction($builder);
}
