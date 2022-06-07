<?php

namespace GlobalPayments\Api\Entities;

class Request
{
    public $endpoint;
    public $requestBody;
    public $queryParams;
    public $httpVerb;
    public $resultsField;

    public function __construct($endpoint, $httpVerb, $requestBody = '', $queryParams = null)
    {
        $this->endpoint = $endpoint;
        $this->httpVerb = $httpVerb;
        $this->requestBody = $requestBody;
        $this->queryParams = $queryParams;
    }
}