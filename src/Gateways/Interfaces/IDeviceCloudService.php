<?php

namespace GlobalPayments\Api\Gateways\Interfaces;

interface IDeviceCloudService
{
    public function processPassThrough($jsonRequest);
}