<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

interface ISAFResponse extends IDeviceResponse
{
    public function getApproved();
    public function getPending();
    public function getDeclined();
}