<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

interface IDeviceMessage
{
    public function getSendBuffer() : array;
}