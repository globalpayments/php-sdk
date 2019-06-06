<?php

namespace GlobalPayments\Api\Terminals\Interfaces;

interface IDeviceResponseHandler
{
    public function mapResponse($gatewayMultipleResponse);
}
