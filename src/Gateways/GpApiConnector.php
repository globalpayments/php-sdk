<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\FileProcessingBuilder;
use GlobalPayments\Api\Builders\FraudBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiMiCRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiInstallmentRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilderFactory;
use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\FileProcessor;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\GpApi\GpApiTokenResponse;
use GlobalPayments\Api\Entities\GpApi\GpApiSessionInfo;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\Reporting\BaseSummary;
use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\MerchantAccountSummary;
use GlobalPayments\Api\Entities\RiskAssessment;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\User;
use GlobalPayments\Api\Gateways\Interfaces\IDeviceCloudService;
use GlobalPayments\Api\Gateways\Interfaces\IFileProcessingService;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Mapping\GpApiMapping;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\Entities\RecurringEntity;
use GlobalPayments\Api\PaymentMethods\Installment;
use GlobalPayments\Api\Builders\InstallmentBuilder;

class GpApiConnector extends RestGateway implements IPaymentGateway, ISecure3dProvider, IPayFacProvider, IFraudCheckService, IDeviceCloudService, IFileProcessingService
{
    const GP_API_VERSION = '2021-03-22';
    const IDEMPOTENCY_HEADER = 'x-gp-idempotency';
    /**
     * @var $gpApiConfig GpApiConfig
     */
    private $gpApiConfig;
    private $accessToken;
    private $builtInMerchantManagementService = true;
    private $config;

    public function supportsOpenBanking() : bool
    {
        return true;
    }

    public function hasBuiltInMerchantManagementService()
    {
        return $this->builtInMerchantManagementService;
    }

    public function __construct(GpApiConfig $gpApiConfig)
    {
        parent::__construct();
        $this->gpApiConfig = $gpApiConfig;
        $this->headers['X-GP-Version'] = self::GP_API_VERSION;
        $this->headers['Accept'] = 'application/json';
        $this->headers['Accept-Encoding'] = 'gzip';
        $this->headers['x-gp-sdk'] = 'php;version=' . $this->getReleaseVersion();
        $this->headers['Content-Type'] = 'charset=UTF-8';
    }

    /**
     * Get the SDK release version
     *
     * @return string|null
     */
    private function getReleaseVersion()
    {
        $filename = dirname(__FILE__) . "/../../metadata.xml";
        if (!file_exists($filename)) {
            return null;
        }
        $xml = simplexml_load_string(file_get_contents($filename));

        return !empty($xml->releaseNumber) ? $xml->releaseNumber : "";
    }

    public function getVersion()
    {
        return Secure3dVersion::ANY;
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
        if ($builder->paymentMethod instanceof AlternativePaymentMethod) {
            return GpApiMapping::mapResponseAPM($response);
        }
        return GpApiMapping::mapResponse($response);
    }

    public function processSecure3d(Secure3dBuilder $builder)
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapResponseSecure3D($response);
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
        if (
            $builder->paymentMethod instanceof TransactionReference &&
            $builder->paymentMethod->paymentMethodType == PaymentMethodType::APM
        ) {
            return GpApiMapping::mapResponseAPM($response);
        }
        return GpApiMapping::mapResponse($response);
    }

    /**
     * Executes the reports
     *
     * @param ReportBuilder $builder
     *
     * @return PagedResult|DepositSummary|DisputeSummary|TransactionSummary|MerchantAccountSummary
     * @throws ApiException
     * @throws GatewayException
     */
    public function processReport(ReportBuilder $builder): BaseSummary
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapReportResponse($response, $builder->reportType);
    }

    /**
     * @param PayFacBuilder $builder
     * @return User
     * @throws ApiException
     * @throws \GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException
     */
    public function processBoardingUser(PayFacBuilder $builder): User
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapMerchantsEndpointResponse($response);
    }

    public function processPayFac(PayFacBuilder $builder)
    {
        throw new UnsupportedTransactionException(sprintf('Method %s not supported by %s', __METHOD__, $this->gpApiConfig->gatewayProvider));
    }

    public function processFraud(FraudBuilder $builder) : RiskAssessment
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapRiskAssessmentResponse($response);
    }

    public function processPassThrough($jsonRequest)
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $requestBuilder = new GpApiMiCRequestBuilder();
        $request = $requestBuilder->buildRequestFromJson($jsonRequest, $this->gpApiConfig);
        if (empty($request)) {
            throw new GatewayException("Request was not generated!");
        }
        $request->endpoint = $this->getMerchantUrl($request) . $request->endpoint;
        $idempotencyKey = !empty($builder->idempotencyKey) ? $builder->idempotencyKey : null;

        return $this->doTransaction(
            $request->httpVerb,
            $request->endpoint,
            $request->requestBody,
            $request->queryParams,
            $idempotencyKey
        );
    }

    public function processFileUpload(FileProcessingBuilder $builder) : FileProcessor
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapFileProcessingResponse($response);
    }

    public function processRecurring(RecurringBuilder $builder): RecurringEntity
    {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        $response = $this->executeProcess($builder);

        return GpApiMapping::mapRecurringEntity($response, $builder->entity);
    }

    /**
    * @var InstallmentBuilder $builder
    *
    * @return Installment|null
    */
    public function processInstallment(InstallmentBuilder $builder): ?Installment
    {
        if (empty($this->accessToken))
            $this->signIn();

        $requestBuilder = new GpApiInstallmentRequestBuilder();
        $request = $requestBuilder->buildRequest($builder, $this->config);

        if ($request != null) {
            $request->endpoint = $this->getMerchantUrl($request) . $request->endpoint;
            $response = $this->doTransaction(
                $request->httpVerb,
                $request->endpoint,
                $request->requestBody,
                $request->queryParams
            );

            return GpApiMapping::mapInstallmentResponse($response, $builder->entity);
        }

        return null;
    }

    private function executeProcess(BaseBuilder $builder)
    {
        $processFactory = new RequestBuilderFactory();
        /**
         * @var IRequestBuilder $requestBuilder
         */
        $requestBuilder = $processFactory->getRequestBuilder($builder, $this->gpApiConfig->gatewayProvider);
        if (empty($requestBuilder)) {
            throw new ApiException("Request builder not found!");
        }
        /**
         * @var GpApiRequest $request
         */
        $request =  $requestBuilder->buildRequest($builder, $this->gpApiConfig);
        $request->endpoint = $this->getMerchantUrl($request) . $request->endpoint;

        if (empty($request)) {
            throw new ApiException("Request was not generated!");
        }
        $idempotencyKey = !empty($builder->idempotencyKey) ? $builder->idempotencyKey : null;
        if (!empty($request::$maskedValues)) {
            $this->maskedRequestData = $request::$maskedValues;
        }
        return $this->doTransaction(
            $request->httpVerb,
            $request->endpoint,
            $request->requestBody,
            $request->queryParams,
            $idempotencyKey
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
     * @param string|null $idempotencyKey
     *
     * @return string
     *
     * @throws GatewayException
     */
    public function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null,
        string $idempotencyKey = null
    ) {
        if (empty($this->accessToken)) {
            $this->signIn();
        }
        if (!empty($idempotencyKey)) {
            $this->headers[self::IDEMPOTENCY_HEADER] = $idempotencyKey;
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
                str_contains($exception->getMessage(), 'NOT_AUTHENTICATED') &&
                !empty($this->gpApiConfig->appKey) &&
                !empty($this->gpApiConfig->appKey)
            ) {
                $this->gpApiConfig->accessTokenInfo = null;
                $this->headers['Authorization'] = null;
                $this->signIn();
                $response = parent::doTransaction(
                    $verb,
                    $endpoint,
                    $data,
                    $queryStringParams
                );

                return json_decode($response);
            }

            throw $exception;
        } finally {
            unset($this->headers[self::IDEMPOTENCY_HEADER]);
        }

        return json_decode($response);
    }

    public function signIn()
    {
        $accessTokenInfo = $this->gpApiConfig->accessTokenInfo;
        if (!empty($accessTokenInfo) && !empty($accessTokenInfo->accessToken)) {
            $this->headers['Authorization'] = sprintf('Bearer %s', $accessTokenInfo->accessToken);
            return;
        }
        $response = $this->getAccessToken();

        $this->accessToken = $response->getToken();
        $this->headers['Authorization'] = sprintf('Bearer %s', $this->accessToken);
        if (!$accessTokenInfo instanceof AccessTokenInfo) {
            $accessTokenInfo = new AccessTokenInfo();
        }
        $accessTokenInfo->merchantId = $response->merchantId;
        if (empty($accessTokenInfo->accessToken)) {
            $accessTokenInfo->accessToken = $response->getToken();
        }

        if (empty($accessTokenInfo->dataAccountID)) {
            $accessTokenInfo->dataAccountID = $response->getDataAccountID();
        }
        if (
            empty($accessTokenInfo->tokenizationAccountID) &&
            empty($accessTokenInfo->tokenizationAccountName)
        ) {
            $accessTokenInfo->tokenizationAccountID = $response->getTokenizationAccountID();
            $accessTokenInfo->tokenizationAccountName = $response->getTokenizationAccountName();
        }

        if (
            empty($accessTokenInfo->transactionProcessingAccountID) &&
            empty($accessTokenInfo->transactionProcessingAccountName)
        ) {
            $accessTokenInfo->transactionProcessingAccountID = $response->getTransactionProcessingAccountID();
            $accessTokenInfo->transactionProcessingAccountName = $response->getTransactionProcessingAccountName();
        }
        if (
            empty($accessTokenInfo->disputeManagementAccountID) &&
            empty($accessTokenInfo->disputeManagementAccountName)
        ) {
            $accessTokenInfo->disputeManagementAccountID = $response->getDisputeManagementAccountID();
        }
        if (
            empty($accessTokenInfo->riskAssessmentAccountID) &&
            empty($accessTokenInfo->riskAssessmentAccountName)
        ) {
            $accessTokenInfo->riskAssessmentAccountID = $response->getRiskAssessmentAccountID();
        }

        if (
            empty($accessTokenInfo->merchantManagementAccountID) &&
            empty($accessTokenInfo->merchantManagementAccountName)
        ) {
            $accessTokenInfo->merchantManagementAccountID = $response->getMerchantManagementAccountID();
        }

        if (
            empty($accessTokenInfo->fileProcessingAccountID) &&
            empty($accessTokenInfo->fileProcessingAccountName)
        ) {
            $accessTokenInfo->fileProcessingAccountID = $response->getFileProcessingAccountID();
        }

        $this->gpApiConfig->accessTokenInfo = $accessTokenInfo;
    }

    /**
     * @return GpApiTokenResponse
     *
     * @throws GatewayException
     */
    public function getAccessToken()
    {
        $this->accessToken = null;

        $request = $this->gpApiConfig->accessTokenProvider->signIn(
            $this->gpApiConfig->appId,
            $this->gpApiConfig->appKey,
            $this->gpApiConfig->secondsToExpire,
            $this->gpApiConfig->intervalToExpire,
            $this->gpApiConfig->permissions
        );
        try {
            $response = parent::doTransaction($request->httpVerb, $request->endpoint, $request->requestBody);
        } catch (GatewayException $gatewayException) {
            throw $gatewayException;
        }

        return new GpApiTokenResponse($response);
    }

    private function getMerchantUrl(GpApiRequest $request)
    {
        return (
            !empty($this->gpApiConfig->merchantId) &&
            strpos($request->endpoint, GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT) === false) ?
            GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $this->gpApiConfig->merchantId : '';
    }
}
