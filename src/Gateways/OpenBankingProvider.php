<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\BankPaymentType;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Mapping\OpenBankingMapping;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\Utils\ArrayUtils;
use GlobalPayments\Api\Utils\GenerationUtils;

class OpenBankingProvider extends RestGateway implements IOpenBankingProvider
{
    /**
     * Merchant ID to authenticate with the gateway
     *
     * @var string
     */
    public $merchantId;

    /**
     * Account ID to authenticate with the gateway
     *
     * @var string
     */
    public $accountId;

    /**
     * Shared secret to authenticate with the gateway
     *
     * @var string
     */
    public $sharedSecret;

    public $shaHashType;

    public function __construct()
    {
        parent::__construct();
        $this->headers['Accept'] = 'application/json';
    }

    public function processOpenBanking(AuthorizationBuilder $builder)
    {
        $httpVerb = $endpoint = $payload = null;
        $timestamp = (new \DateTime())->format("YmdHis");
        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();
        $amount = ($builder->amount !== null) ? preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)) : null;
        /** @var BankPayment $paymentMethod */
        $paymentMethod = $builder->paymentMethod;
        switch ($builder->transactionType) {
            case TransactionType::SALE:
                $httpVerb = 'POST';
                $endpoint = '/payments';
                $bankPaymentType = !empty($paymentMethod->bankPaymentType) ?
                    $paymentMethod->bankPaymentType : self::getBankPaymentType($builder->currency);
                $hash =  implode('.', [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
                        $amount,
                        $builder->currency,
                        !empty($paymentMethod->sortCode) && $bankPaymentType == BankPaymentType::FASTERPAYMENTS ?
                            $paymentMethod->sortCode : '',
                        !empty($paymentMethod->accountNumber) && $bankPaymentType == BankPaymentType::FASTERPAYMENTS ?
                            $paymentMethod->accountNumber : '',
                        !empty($paymentMethod->iban) && $bankPaymentType == BankPaymentType::SEPA ? $paymentMethod->iban : '',
                    ]);
                $this->setAuthorizationHeader($hash);

                $payload = [
                    'request_timestamp' => $timestamp,
                    'merchant_id' => $this->merchantId,
                    'account_id' => $this->accountId,
                    'order' => [
                        'id' => $orderId,
                        'currency' => $builder->currency,
                        'amount' => $amount,
                        'description' => $builder->description
                    ],
                    'payment' => [
                        'scheme' => $bankPaymentType,
                        'destination' => [
                            'account_number' => $bankPaymentType == BankPaymentType::FASTERPAYMENTS ?
                                $paymentMethod->accountNumber : '',
                            'sort_code' => $bankPaymentType == BankPaymentType::FASTERPAYMENTS ?
                                $paymentMethod->sortCode : '',
                            'iban' => $bankPaymentType == BankPaymentType::SEPA ? $paymentMethod->iban : '',
                            'name' => $paymentMethod->accountName
                            ],
                        'remittance_reference' => [
                            'type' => $builder->remittanceReferenceType,
                            'value' => $builder->remittanceReferenceValue
                            ]
                        ],
                    'return_url' => $paymentMethod->returnUrl,
                    'status_url' => $paymentMethod->statusUpdateUrl
                    ];
                break;
            default:
                break;
        }
        $payload = ArrayUtils::array_remove_empty($payload);
        $payload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        try {
            $response = parent::doTransaction($httpVerb, $endpoint, $payload);
        } catch (GatewayException $gatewayException) {
            throw $gatewayException;
        }

        return OpenBankingMapping::mapResponse($response);
    }

    public function processReport(TransactionReportBuilder $builder)
    {
        $httpVerb = $endpoint = null;
        $queryParams = [];
        $timestamp = (new \DateTime())->format("YmdHis");
        switch ($builder->reportType) {
            case ReportType::FIND_BANK_PAYMENT:
                $httpVerb = 'GET';
                $endpoint = '/payments';
                $accountId = empty($builder->searchBuilder->bankPaymentId) ? $this->accountId : '';
                $hash = implode('.', [
                    $timestamp,
                    $this->merchantId,
                    $accountId,
                    !empty($builder->searchBuilder->bankPaymentId) ?
                        $builder->searchBuilder->bankPaymentId : '',
                    !empty($builder->searchBuilder->startDate) ?
                        $builder->searchBuilder->startDate->format("YmdHis") : '',
                    !empty($builder->searchBuilder->endDate) ?
                        $builder->searchBuilder->endDate->format("YmdHis") : '',
                    isset($builder->searchBuilder->returnPii) ?
                        ($builder->searchBuilder->returnPii === true ? 'True' : 'False') : ''
                ]);
                $this->setAuthorizationHeader($hash);
                $queryParams = [
                    'timestamp' => $timestamp,
                    'merchantId' => $this->merchantId,
                    'accountId' => $accountId,
                    'obTransId' => !empty($builder->searchBuilder->bankPaymentId) ?
                        $builder->searchBuilder->bankPaymentId : '',
                    'startDateTime' => !empty($builder->searchBuilder->startDate) ?
                        $builder->searchBuilder->startDate->format("YmdHis") : '',
                    'endDateTime' => !empty($builder->searchBuilder->endDate) ?
                        $builder->searchBuilder->endDate->format("YmdHis") : '',
                    'transactionState' => !empty($builder->searchBuilder->transactionStatus) ?
                        $builder->searchBuilder->transactionStatus : '',
                    'returnPii' => isset($builder->searchBuilder->returnPii) ?
                        ($builder->searchBuilder->returnPii === true ? 'True' : 'False') : ''
                ];
                break;
            default:
                break;
        }
        $queryParams = ArrayUtils::array_remove_empty($queryParams);

        try {
            $response = parent::doTransaction($httpVerb, $endpoint, null, $queryParams);
        } catch (GatewayException $gatewayException) {
            throw $gatewayException;
        }

        return OpenBankingMapping::mapReportResponse($response, $builder->reportType);
    }

    private function setAuthorizationHeader($hash)
    {
        $hash = hash($this->shaHashType, $hash) . '.' . $this->sharedSecret;
        $this->headers['Authorization'] = sprintf('%s %s', $this->shaHashType, hash($this->shaHashType, $hash));
    }

    /**
     * Return the type of the open banking transaction
     *
     * @param string $currency
     *
     * @return string|null
     */
    public static function getBankPaymentType($currency)
    {
        switch ($currency) {
            case 'EUR':
                return BankPaymentType::SEPA;
            case 'GBP':
                return BankPaymentType::FASTERPAYMENTS;
            default:
                return null;
        }
    }
}