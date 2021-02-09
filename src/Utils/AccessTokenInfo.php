<?php


namespace GlobalPayments\Api\Utils;


use GlobalPayments\Api\Entities\GpApi\AccessToken;
use GlobalPayments\Api\Entities\GpApi\AccessTokenRequest;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\Gateways\RestGateway;
use GlobalPayments\Api\Gateways\RestGatewayWithCompression;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesConfig;

class AccessTokenInfo extends RestGateway
{
    public $appId;
    public $appKey;
    public $servicePoint;
    public $secondsToExpire;
    public $intervalToExpire;

    /**
     * @var $accessToken AccessToken
     */
    private $accessToken;

    private $dataAccountName;
    private $disputeManagementAccountName;
    private $tokenizationAccountName;
    private $transactionProcessingAccountName;

    const DATA_ACCOUNT_NAME_PREFIX = 'DAA_';
    const DISPUTE_MANAGEMENT_ACCOUNT_NAME_PREFIX = 'DIA_';
    const TOKENIZATION_ACCOUNT_NAME_PREFIX = 'TKA_';
    const TRANSACTION_PROCESSING_ACCOUNT_NAME_PREFIX = 'TRA_';

    /**
     * @return AccessToken
     * @throws \GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function generateAccessToken()
    {
        $this->headers = [
            'X-GP-VERSION' => GpApiConnector::GP_API_VERSION,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip'
        ];
        $endPoint = $this->servicePoint . GpApiConnector::ACCESS_TOKEN_ENDPOINT;
        $requestBody = new AccessTokenRequest(
            $this->appId,
            $this->generateNonce(),
            $this->generateSecret(),
            'client_credentials',
            $this->secondsToExpire,
            $this->intervalToExpire
        );

        $request = $this->doTransaction("POST", $endPoint, $requestBody, null);
        $this->accessToken = new AccessToken(
            $request->token,
            $request->type,
            $request->time_created,
            $request->seconds_to_expire
        );
        foreach ($request->scope->accounts as $account) {
            switch (substr($account->id, 0, 4)) {
                case self::DATA_ACCOUNT_NAME_PREFIX:
                    $this->dataAccountName = $account->name;
                    break;
                case self::DISPUTE_MANAGEMENT_ACCOUNT_NAME_PREFIX:
                    $this->disputeManagementAccountName = $account->name;
                    break;
                case self::TOKENIZATION_ACCOUNT_NAME_PREFIX:
                    $this->tokenizationAccountName = $account->name;
                    break;
                case self::TRANSACTION_PROCESSING_ACCOUNT_NAME_PREFIX:
                    $this->transactionProcessingAccountName = $account->name;
                    break;
            }

        }
        return $this->accessToken;
    }

    public function getAccessToken()
    {
        if (empty($this->accessToken) || ($this->accessToken->seconds_to_expire < 100)) {
            $this->generateAccessToken();
        }
        return $this->accessToken;
    }

    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function initialize(GpApiConfig $servicesConfig)
    {
        $this->appId = $servicesConfig->getAppId();
        $this->appKey = $servicesConfig->getAppKey();
        $this->servicePoint = $servicesConfig->serviceUrl;
        $this->secondsToExpire = $servicesConfig->getSecondsToExpire();
        $this->intervalToExpire = $servicesConfig->getIntervalToExpire();
    }

    public function getDataAccountName()
    {
        if (empty($this->dataAccountName)) {
            $this->generateAccessToken();
        }
        return $this->dataAccountName;
    }

    public function getDisputeManagementAccountName()
    {
        if (empty($this->disputeManagementAccountName)) {
            $this->generateAccessToken();
        }
        return $this->disputeManagementAccountName;
    }

    public function getTokenizationAccountName()
    {
        if (empty($this->tokenizationAccountName)) {
            $this->generateAccessToken();
        }
        return $this->tokenizationAccountName;
    }

    public function getTransactionProcessingAccountName()
    {
        if (empty($this->transactionProcessingAccountName)) {
            $this->generateAccessToken();
        }
        return $this->transactionProcessingAccountName;
    }

    /**
     * @param mixed $dataAccountName
     */
    public function setDataAccountName($dataAccountName)
    {
        $this->dataAccountName = $dataAccountName;
    }

    /**
     * @param mixed $disputeManagementAccountName
     */
    public function setDisputeManagementAccountName($disputeManagementAccountName)
    {
        $this->disputeManagementAccountName = $disputeManagementAccountName;
    }

    /**
     * @param mixed $tokenizationAccountName
     */
    public function setTokenizationAccountName($tokenizationAccountName)
    {
        $this->tokenizationAccountName = $tokenizationAccountName;
    }

    /**
     * @param mixed $transactionProcessingAccountName
     */
    public function setTransactionProcessingAccountName($transactionProcessingAccountName)
    {
        $this->transactionProcessingAccountName = $transactionProcessingAccountName;
    }

    private function generateSecret()
    {
        return hash('SHA512', $this->generateNonce() . $this->appKey);
    }

    private function generateNonce()
    {
        $base = new \DateTime();
        return $base->format(\DateTime::RFC3339);
    }

    protected function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null
    ) {
        $response = parent::doTransaction($verb, $endpoint, $data, $queryStringParams);
        return json_decode($response);
    }
}