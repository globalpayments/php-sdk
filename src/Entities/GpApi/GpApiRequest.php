<?php

namespace GlobalPayments\Api\Entities\GpApi;

class GpApiRequest
{
    const ACCESS_TOKEN_ENDPOINT = '/accesstoken';
    const TRANSACTION_ENDPOINT = '/transactions';
    const PAYMENT_METHODS_ENDPOINT = '/payment-methods';
    const VERIFICATIONS_ENDPOINT = '/verifications';
    const DEPOSITS_ENDPOINT = '/settlement/deposits';
    const DISPUTES_ENDPOINT = '/disputes';
    const SETTLEMENT_DISPUTES_ENDPOINT = '/settlement/disputes';
    const SETTLEMENT_TRANSACTIONS_ENDPOINT = '/settlement/transactions';
    const AUTHENTICATIONS_ENDPOINT = '/authentications';
    const BATCHES_ENDPOINT = '/batches';
    const ACTIONS_ENDPOINT = '/actions';

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