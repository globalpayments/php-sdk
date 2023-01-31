<?php

namespace GlobalPayments\Api\Entities\TransactionApi;

class TransactionApiRequest
{
    const CREDITSALE      = 'creditsales';
    const CREDITSALEREF   = 'creditsales/reference_id';
    const CREDITSALEVOID  = 'voids';
    const CREDITAUTH      = 'creditauths';
    const CHECKREFUND     = 'checkrefunds';
    const CHECKREFUNDREF  = 'checkrefunds/reference_id';
    const CHECKSALES      = 'checksales';
    const CHECKSALESREF   = 'checksales/reference_id';
    const CREDITREFUND    = 'creditreturns';
    const CREDITREFUNDREF = 'creditreturns/reference_id';

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
