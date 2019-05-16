<?php

namespace GlobalPayments\Api\Terminals\Interfaces;

interface IDeviceCommInterface
{
    public function connect();
    
    public function disconnect();
    
    public function send($message, $requestType = null);
    
    public function parseResponse($gatewayResponse);
}
