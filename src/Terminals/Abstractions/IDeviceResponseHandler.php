<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

interface IDeviceResponseHandler
{
    public function mapResponse($messageReader);
}
