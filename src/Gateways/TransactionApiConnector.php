<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\{
    AuthorizationBuilder,
    ManagementBuilder,
    ReportBuilder
};
use GlobalPayments\Api\Entities\{IRequestBuilder, Transaction};
use GlobalPayments\Api\Mapping\TransactionApiMapping;
use GlobalPayments\Api\Entities\Exceptions\{ApiException, GatewayException};
use GlobalPayments\Api\Entities\Reporting\{
    DepositSummary,
    DisputeSummary,
    TransactionSummary
};
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiRequest;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilderFactory;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;

class TransactionApiConnector extends RestGateway implements IPaymentGateway
{
    /**
     * @var $transactionApiConfig TransactionApiConfig
     */
    private $transactionApiConfig;
    private $accessToken;

    public function __construct(TransactionApiConfig $transactionApiConfig)
    {
        parent::__construct();
        $this->transactionApiConfig = $transactionApiConfig;
    }

    public function supportsOpenBanking() : bool
    {
        return false;
    }

    /**
     * Serializes and executes authorization transactions
     *
     * @param AuthorizationBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder)
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }

        $response = $this->executeProcess($builder);
        return TransactionApiMapping::mapResponse($response);
    }

    /**
     * Serializes and executes follow up transactions
     *
     * @param ManagementBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function manageTransaction(ManagementBuilder $builder)
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);
        return TransactionApiMapping::mapResponse($response);
    }

    /**
     * Executes the reports
     *
     * @param ReportBuilder $builder
     *
     * @return DepositSummary|DisputeSummary|TransactionSummary
     * @throws ApiException
     * @throws GatewayException
     */
    public function processReport(ReportBuilder $builder)
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return TransactionApiMapping::mapReportResponse($response, $builder->reportType);
    }

    private function executeProcess($builder)
    {
        $processFactory = new RequestBuilderFactory();
        /**
         * @var IRequestBuilder $requestBuilder
         */
        $requestBuilder = $processFactory->getRequestBuilder($builder, $this->transactionApiConfig->gatewayProvider);
        if (empty($requestBuilder)) {
            throw new ApiException("Request builder not found!");
        }
        /**
         * @var TransactionApiRequest $request
         */
        $request = $requestBuilder->buildRequest($builder, $this->transactionApiConfig);

        if (empty($request)) {
            throw new ApiException("Request was not generated!");
        }

        return $this->doTransaction(
            $request->httpVerb,
            $request->endpoint,
            $request->requestBody,
            $request->queryParams
        );
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        // TODO: Implement serializeRequest() method.
    }

    /**
     * @param string $verb
     * @param string $endpoint
     * @param null $data
     * @param array|null $queryStringParams
     *
     * @return object
     *
     * @throws GatewayException
     */
    public function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null
    ) {
        if (empty($this->accessToken)) {
            $this->signIn();
        }

        //weird bug where if you populate the contentType header on this endpoint it throws a 502 bad gateway error
        //if you don't send it the error is even weirder, you just have to send it empty
        if (
            strpos($endpoint, 'settlement') !== false ||
            (strpos($endpoint, 'disputes') !== false && strpos($endpoint, 'challenge') == false)
        ) {
            $this->contentType = '';
        }

        try {
            $response = parent::doTransaction(
                $verb,
                $endpoint,
                $data,
                $queryStringParams
            );
        } catch (GatewayException $exception) {
            if (
                strpos($exception->getMessage(), 'NOT_AUTHENTICATED') !== false
                && !empty($this->transactionApiConfig->appKey)
            ) {
                $this->accessToken = null;
                $this->signIn();
                return parent::doTransaction(
                    $verb,
                    $endpoint,
                    $data,
                    $queryStringParams
                );
            }

            throw $exception;
        }
        $this->accessToken = null;
        return json_decode($response);
    }

    /**
     *
     * Transaction API token generation
     */
    public function signIn()
    {
        // Transaction API get config credentials
        $accountCredential = $this->transactionApiConfig->accountCredential;
        $apiSecret         = $this->transactionApiConfig->apiSecret;
        $region            = $this->transactionApiConfig->country;
        $apiKey            = $this->transactionApiConfig->apiKey;
        $apiVersion        = $this->transactionApiConfig->apiVersion;
        $apiPartnerName    = $this->transactionApiConfig->apiPartnerName;

        if (!empty($this->accessToken)) {
            $this->headers['Authorization'] = sprintf('AuthToken %s', $this->accessToken);
            return;
        }

        // token generation
        $this->accessToken = $this->generateAuthToken($accountCredential, $apiSecret, $region);
        $this->headers['Authorization'] = sprintf('AuthToken %s', $this->accessToken);
        $this->headers['X-GP-Api-Key'] = $apiKey;
        $this->headers['X-GP-Version'] = $apiVersion;
        $this->headers['X-GP-Partner-App-Name'] = $apiPartnerName;
    }

    /**
     * @return encodedData
     *
     * Transaction API base64 encoding
     */
    private function base64url_encode($data)
    {
        return strtr(base64_encode($data), '+/', '-_');
    }

    /**
     * @param string $accountCredential
     * @param string $apiSecret
     * @param string $region
     *
     * @return string $authToken
     *
     */
    public function generateAuthToken($accountCredential, $apiSecret, $region)
    {
        $headerObj = new \stdClass();
        $headerObj->alg = 'HS256';
        $headerObj->typ = 'JWT';
        $headerJSON = $this->base64url_encode(json_encode($headerObj));
        $microseconds = floor(microtime(true) * 1000);
        $jwtPayloadObj = new \stdClass();
        $jwtPayloadObj->account_credential = $accountCredential;
        $jwtPayloadObj->region = $region;
        $jwtPayloadObj->type = 'AuthTokenV2';
        $jwtPayloadObj->ts = $microseconds;
        $payloadJSON = $this->base64url_encode(json_encode($jwtPayloadObj));

        $signature = $this->base64url_encode(hash_hmac('sha256', sprintf('%s.%s', $headerJSON, $payloadJSON), $apiSecret, true));
        $authToken = sprintf('%s.%s.%s', $headerJSON, $payloadJSON, $signature);
        return $authToken;
    }
}
