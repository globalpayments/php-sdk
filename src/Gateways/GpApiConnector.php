<?php


namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\Enums\GpApi\CaptureMode;
use GlobalPayments\Api\Entities\Enums\GpApi\EntryMode;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\Authentication3DSRequest;
use GlobalPayments\Api\Entities\GpApi\CreatePaymentMethodRequest;
use GlobalPayments\Api\Entities\GpApi\CreatePaymentRequest;
use GlobalPayments\Api\Entities\GpApi\ManageCaptureRequest;
use GlobalPayments\Api\Entities\GpApi\ManageRefundRequest;
use GlobalPayments\Api\Entities\GpApi\ManageReversalRequest;
use GlobalPayments\Api\Entities\GpApi\ReportingFindDeposits;
use GlobalPayments\Api\Entities\GpApi\ReportingFindSettlementTransactions;
use GlobalPayments\Api\Entities\GpApi\ReportingFindTransactions;
use GlobalPayments\Api\Entities\Reporting\DisputeSummaryList;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Mapping\GpApiMapping;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiConnector extends RestGateway implements IPaymentGateway, ISecure3dProvider
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
    const GP_API_VERSION = '2020-12-22';
    const IDEMPOTENCY_HEADER = 'x-gp-idempotency';
    /**
     * @var $gpApiConfig GpApiConfig
     */
    private $gpApiConfig;
    private $accessToken;

    public function __construct(GpApiConfig $gpApiConfig)
    {
        parent::__construct();
        $this->gpApiConfig = $gpApiConfig;
        $this->accessToken = $gpApiConfig->getAccessTokenInfo()->getAccessToken();
        $this->headers['X-GP-VERSION'] = self::GP_API_VERSION;
        $this->headers['Authorization'] = $this->accessToken->composeAuthorizationHeader();
        $this->headers['Accept'] = 'application/json';
        $this->headers['Accept-Encoding'] = 'gzip';
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
        $entryMode = $this->getEntryMode($builder);
        $captureMode = $this->getCaptureMode($builder);

        if ($builder->transactionType == TransactionType::SALE ||
            $builder->transactionType == TransactionType::REFUND ||
            $builder->transactionType == TransactionType::AUTH) {

            $transaction = CreatePaymentRequest::createFromAuthorizationBuilder(
                $builder,
                $this->gpApiConfig,
                $entryMode,
                $captureMode
            );
            $response = $this->doTransaction(
                "POST",
                self::TRANSACTION_ENDPOINT,
                $transaction,
                null,
                $builder->idempotencyKey
            );
        } elseif ($builder->transactionType == TransactionType::VERIFY) {
            //or in other words, if we need to tokenize
            if ($builder->requestMultiUseToken) {
                $transaction = CreatePaymentMethodRequest::createFromAuthorizationBuilder(
                    $builder,
                    $this->gpApiConfig->getAccessTokenInfo()
                );
                $response = $this->doTransaction(
                    "POST",
                    self::PAYMENT_METHODS_ENDPOINT,
                    $transaction,
                    null,
                    $builder->idempotencyKey
                );
                //otherwise we just retrieve the payment method behind the token
            } else {
                if ($builder->paymentMethod instanceof ITokenizable && !empty($builder->paymentMethod->token)) {
                    $response = $this->doTransaction(
                        "GET",
                        self::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token,
                        null,
                        null,
                        $builder->idempotencyKey
                    );
                } else {
                    $verificationData = (object)[
                        'account_name' => $this->gpApiConfig->getAccessTokenInfo()->getTransactionProcessingAccountName(),
                        'channel' => $this->gpApiConfig->getChannel(),
                        'reference' => !empty($builder->clientTransactionId) ?
                            $builder->clientTransactionId : GenerationUtils::getGuid(),
                        'currency' => $builder->currency,
                        'country' => !empty($builder->billingAddress) ?
                            $builder->billingAddress->country : $this->gpApiConfig->getCountry(),
                        'payment_method' => CreatePaymentRequest::createPaymentMethodParam($builder, $entryMode)
                    ];

                    $response = $this->doTransaction(
                        "POST",
                        self::VERIFICATIONS_ENDPOINT,
                        $verificationData,
                        null,
                        $builder->idempotencyKey
                    );
                }
            }
        }

        return GpApiMapping::mapResponse($response);
    }

    public function processSecure3d(Secure3dBuilder $builder)
    {
        if (empty($this->accessToken)) {
            $this->accessToken = $this->gpApiConfig->getAccessTokenInfo()->getAccessToken();
        }

        switch ($builder->transactionType)
        {
            case TransactionType::VERIFY_ENROLLED:
                $data = Authentication3DSRequest::verifyEnrolled($builder, $this->gpApiConfig);
                $verb = 'POST';
                $endpoint = self::AUTHENTICATIONS_ENDPOINT;
                break;
            case TransactionType::INITIATE_AUTHENTICATION:
                $data = Authentication3DSRequest::initiateAuthenticationData($builder, $this->gpApiConfig);
                $verb = 'POST';
                $endpoint = self::AUTHENTICATIONS_ENDPOINT . "/{$builder->getServerTransactionId()}/initiate";
                break;
            case  TransactionType::VERIFY_SIGNATURE:
                $verb = 'GET';
                $endpoint = self::AUTHENTICATIONS_ENDPOINT . "/{$builder->getServerTransactionId()}/result";
                $data = null;
                break;
            default:
                throw new ApiException("Transaction type not supported!");
        }

        $response = $this->doTransaction(
            $verb,
            $endpoint,
            $data,
            null,
            $builder->idempotencyKey
        );

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
        switch ($builder->transactionType) {
            case TransactionType::DETOKENIZE:
                $requestBody = null;
                $endpoint = self::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token . '/detokenize';
                $verb = 'POST';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::TOKEN_DELETE:
                $requestBody = null;
                $endpoint = self::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'DELETE';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::TOKEN_UPDATE:
                $requestBody = CreatePaymentMethodRequest::createFromManagementBuilder($builder);
                $endpoint = self::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'PATCH';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::REFUND:
                $requestBody = ManageRefundRequest::createFromManagementBuilder($builder);
                $endpoint = self::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/refund';
                $verb = 'POST';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::REVERSAL:
                $requestBody = ManageReversalRequest::createFromManagementBuilder($builder);
                $endpoint = self::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/reversal';
                $verb = 'POST';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::CAPTURE:
                $requestBody = ManageCaptureRequest::createFromManagementBuilder($builder);
                $endpoint = self::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/capture';
                $verb = 'POST';
                $idempotencyKey = $builder->idempotencyKey;
                break;
            case TransactionType::DISPUTE_ACCEPTANCE:
                $requestBody = null;
                $endpoint = self::DISPUTES_ENDPOINT . '/' . $builder->disputeId . '/acceptance';
                $verb = 'POST';
                $idempotencyKey = null;
                break;
            case TransactionType::DISPUTE_CHALLENGE:
                $requestBody = (object)['documents' => $builder->disputeDocuments];
                $endpoint = self::DISPUTES_ENDPOINT . '/' . $builder->disputeId . '/challenge';
                $verb = 'POST';
                $idempotencyKey = null;
                break;
            default:
                throw new ApiException("Transaction type not supported!");
        }

        $response = $this->doTransaction(
            $verb,
            $endpoint,
            $requestBody,
            null,
            $idempotencyKey
        );

        return GpApiMapping::mapResponse($response);
    }

    public function processReport(ReportBuilder $builder)
    {
        switch ($builder->reportType) {
            case ReportType::TRANSACTION_DETAIL:
                $response = $this->doTransaction(
                    'GET',
                    self::TRANSACTION_ENDPOINT . '/' . $builder->transactionId,
                    null,
                    null
                );
                break;
            case ReportType::FIND_TRANSACTIONS:
                $queryString = ReportingFindTransactions::createFromTransactionReportBuilder($builder);
                $response = $this->doTransaction(
                    'GET',
                    self::TRANSACTION_ENDPOINT,
                    null,
                    $queryString
                );
                break;
            case ReportType::DEPOSIT_DETAIL:
                $response = $this->doTransaction(
                'GET',
                self::DEPOSITS_ENDPOINT . '/' . $builder->searchBuilder->depositId,
                null,
                null
                );
                break;
            case ReportType::FIND_DEPOSITS:
                $queryString = ReportingFindDeposits::createFromTransactionReportBuilder(
                    $builder,
                    $this->gpApiConfig->getAccessTokenInfo()
                );
                $response = $this->doTransaction(
                    'GET',
                    self::DEPOSITS_ENDPOINT,
                    null,
                    $queryString
                );
                break;
            case ReportType::FIND_DISPUTES:
                $queryString = [
                    'page' => $builder->page,
                    'page_size' => $builder->pageSize,
                    'order_by' => $builder->disputeOrderBy,
                    'order' => $builder->disputeOrder,
                    'arn' => $builder->searchBuilder->aquirerReferenceNumber,
                    'brand' => $builder->searchBuilder->cardBrand,
                    'status' => $builder->searchBuilder->disputeStatus,
                    'stage' => $builder->searchBuilder->disputeStage,
                    'from_stage_time_created' => !empty($builder->searchBuilder->startStageDate) ?
                        $builder->searchBuilder->startStageDate->format('Y-m-d') : null,
                    'to_stage_time_created' => !empty($builder->searchBuilder->endStageDate) ?
                        $builder->searchBuilder->endStageDate->format('Y-m-d') : null,
                    'adjustment_funding' => $builder->searchBuilder->adjustmentFunding,
                    'from_adjustment_time_created' => !empty($builder->searchBuilder->startAdjustmentDate) ?
                        $builder->searchBuilder->startAdjustmentDate->format('Y-m-d') : null,
                    'to_adjustment_time_created' => !empty($builder->searchBuilder->endAdjustmentDate) ?
                        $builder->searchBuilder->endAdjustmentDate->format('Y-m-d') : null,
                    'system.mid' => $builder->searchBuilder->merchantId,
                    'system.hierarchy' => $builder->searchBuilder->systemHierarchy
                ];
                $response = $this->doTransaction(
                    'GET',
                    self::DISPUTES_ENDPOINT,
                    null,
                    $queryString
                );
                break;
            case ReportType::FIND_SETTLEMENT_DISPUTES:
                $queryString = [
                    'account_name' => $this->gpApiConfig->getAccessTokenInfo()->getDataAccountName(),
                    'page' => $builder->page,
                    'page_size' => $builder->pageSize,
                    'order_by' => $builder->disputeOrderBy,
                    'order' => $builder->disputeOrder,
                    'arn' => $builder->searchBuilder->aquirerReferenceNumber,
                    'brand' => $builder->searchBuilder->cardBrand,
                    'STATUS' => $builder->searchBuilder->disputeStatus,
                    'stage' => $builder->searchBuilder->disputeStage,
                    'from_stage_time_created' => !empty($builder->searchBuilder->startStageDate) ?
                        $builder->searchBuilder->startStageDate->format('Y-m-d') : null,
                    'to_stage_time_created' => !empty($builder->searchBuilder->endStageDate) ?
                        $builder->searchBuilder->endStageDate->format('Y-m-d') : null,
                    'adjustment_funding' => $builder->searchBuilder->adjustmentFunding,
                    'from_adjustment_time_created' => !empty($builder->searchBuilder->startAdjustmentDate) ?
                        $builder->searchBuilder->startAdjustmentDate->format('Y-m-d') : null,
                    'to_adjustment_time_created' => !empty($builder->searchBuilder->endAdjustmentDate) ?
                        $builder->searchBuilder->endAdjustmentDate->format('Y-m-d') : null,
                    'system.mid' => $builder->searchBuilder->merchantId,
                    'system.hierarchy' => $builder->searchBuilder->systemHierarchy
                ];
                $response = $this->doTransaction(
                    'GET',
                    self::SETTLEMENT_DISPUTES_ENDPOINT,
                    null,
                    $queryString
                );
                break;
            case ReportType::DISPUTE_DETAIL:
                $response = $this->doTransaction(
                    'GET',
                    self::DISPUTES_ENDPOINT . '/' . $builder->searchBuilder->disputeId,
                    null,
                    null
                );
                break;
            case ReportType::SETTLEMENT_DISPUTE_DETAIL:
                $response = $this->doTransaction(
                    'GET',
                    self::SETTLEMENT_DISPUTES_ENDPOINT . '/' . $builder->searchBuilder->settlementDisputeId,
                    null,
                    null
                );
                break;
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS:
                $queryString = ReportingFindSettlementTransactions::createFromTransactionReportBuilder(
                    $builder,
                    $this->gpApiConfig->getAccessTokenInfo()
                );
                $response = $this->doTransaction(
                    'GET',
                    self::SETTLEMENT_TRANSACTIONS_ENDPOINT,
                    null,
                    $queryString
                );
                break;
            default:
                throw new ApiException("Report type not supported!");
        }

        return $this->mapReportResponse($response, $builder->reportType);
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        // TODO: Implement serializeRequest() method.
    }

    /**
     * @param $response
     * @param $reportType ReportType
     */
    protected function mapReportResponse($response, $reportType)
    {
        switch ($reportType) {
            case ReportType::TRANSACTION_DETAIL:
                $report = GpApiMapping::mapTransactionSummary($response);
                break;
            case ReportType::FIND_TRANSACTIONS:
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS:
                $report = array();
                foreach ($response->transactions as $transaction) {
                    array_push($report, GpApiMapping::mapTransactionSummary($transaction));
                }
                break;
            case ReportType::DEPOSIT_DETAIL:
                $report = GpApiMapping::mapDepositSummary($response);
                break;
            case ReportType::FIND_DEPOSITS:
                $report = array();
                foreach ($response->deposits as $deposit) {
                    array_push($report, GpApiMapping::mapDepositSummary($deposit));
                }
                break;
            case ReportType::DISPUTE_DETAIL:
            case ReportType::SETTLEMENT_DISPUTE_DETAIL:
                $report = new DisputeSummaryList();
                $report->append(GpApiMapping::mapDisputeSummary($response));
                break;
            case ReportType::FIND_DISPUTES:
            case ReportType::FIND_SETTLEMENT_DISPUTES:
                $report = new DisputeSummaryList();
                foreach ($response->disputes as $dispute) {
                    $report->append(GpApiMapping::mapDisputeSummary($dispute));
                }
                break;
            default:
                throw new ApiException("Report type not supported!");
        }

        return $report;
    }

    protected function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null,
        string $idempotencyKey = null
    ) {
        if (empty($this->accessToken)) {
            $this->accessToken = $this->gpApiConfig->getAccessTokenInfo()->getAccessToken();
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
            if ($exception->responseCode == 'NOT_AUTHENTICATED') {
                $this->accessToken = $this->gpApiConfig->getAccessTokenInfo()->getAccessToken();
                return parent::doTransaction(
                    $verb,
                    $endpoint,
                    $data,
                    $queryStringParams
                );
            }

            throw $exception;
        } finally {
            unset($this->headers[self::IDEMPOTENCY_HEADER]);
        }

        return json_decode($response);
    }

    private function getEntryMode(AuthorizationBuilder $builder)
    {
        if ($builder->paymentMethod instanceof ICardData) {
            if ($builder->paymentMethod->readerPresent) {
                return $builder->paymentMethod->cardPresent ? EntryMode::MANUAL : EntryMode::IN_APP;
            } else {
                return $builder->paymentMethod->cardPresent ? EntryMode::MANUAL : EntryMode::ECOM;
            }
        } elseif ($builder->paymentMethod instanceof ITrackData) {
            if (!empty($builder->tagData)) {
                return ($builder->paymentMethod->entryMethod == EntryMode::SWIPE) ?
                    EntryMode::CHIP : EntryMode::CONTACTLESS_CHIP;
            } elseif (!empty($builder->hasEmvFallbackData())) {
                return EntryMode::CONTACTLESS_SWIPE;
            } else {
                return EntryMode::SWIPE;
            }
        }

        return EntryMode::ECOM;
    }

    private function getCaptureMode(AuthorizationBuilder $builder)
    {
        if ($builder->multiCapture) {
            return CaptureMode::MULTIPLE;
        }
        if ($builder->transactionType == TransactionType::AUTH) {
            return CaptureMode::LATER;
        }
        return CaptureMode::AUTO;
    }
}
