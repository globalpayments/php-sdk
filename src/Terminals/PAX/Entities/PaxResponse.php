<?php

namespace GlobalPayments\Api\Terminals\PAX\Entities;

/**
 * Heartland PAX response data
 */
class PaxResponse
{

    public $deviceId;
    public $response;
    public $resultCode;
    public $resultText;
    public $responseData;
    public $requestId;
    
    // Internal
    public $status;
    public $command;
    public $version;
    public $deviceResponseCode;
    public $deviceResponseText;
}
