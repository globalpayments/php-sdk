<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Transaction;

class HostedService
{

    /**
     * Shared secret to authenticate with the gateway
     */
    public string $sharedSecret;
    public ShaHashType|string $shaHashType = ShaHashType::SHA1;
    private static array $supportedShaType = [
        ShaHashType::SHA1,
        ShaHashType::SHA256
    ];

    /**
     * Instatiates a new object
     *
     * @param GpEcomConfig $config Service config
     *
     */
    public function __construct(GpEcomConfig $config)
    {
        if (!in_array($config->shaHashType, self::$supportedShaType)) {
            throw new ApiException(sprintf("%s not supported. Please check your code and the Developers Documentation.", $config->shaHashType));
        }
        ServicesContainer::configureService($config);
        $this->sharedSecret = $config->sharedSecret;
        $this->shaHashType = $config->shaHashType;
    }

    /**
     * Creates an authorization builder with type
     * `TransactionType::CREDIT_AUTH`
     *
     * @param float|string|nullCardUtils $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function authorize(string|float $amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::AUTH))
            ->withAmount($amount);
    }

    /**
     * Authorizes the payment method and captures the entire authorized amount
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function charge($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::SALE))
            ->withAmount($amount);
    }

    /**
     * Verifies the payment method
     *
     * @return AuthorizationBuilder
     */
    public function verify($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::VERIFY))
            ->withAmount($amount);
    }

    public function void($transaction = null)
    {
        if (!($transaction instanceof TransactionReference)) {
            $transactionReference = new TransactionReference();
            $transactionReference->transactionId = $transaction;
            $transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
            $transaction = $transactionReference;
        }

        return (new ManagementBuilder(TransactionType::VOID))
            ->withPaymentMethod($transaction);
    }

    private function mapTransactionStatusResponse($response) : array
    {
        return [
            'ACCOUNT_HOLDER_NAME' => $response['accountholdername'] ?? '',
            'ACCOUNT_NUMBER' => $response['accountnumber'] ?? '',
            'TIMESTAMP' => $response['timestamp'] ?? '',
            'MERCHANT_ID' => $response['merchantid'] ?? '',
            'BANK_CODE' => $response['bankcode'] ?? '',
            'BANK_NAME' => $response['bankname'] ?? '',
            'HPP_CUSTOMER_BIC' => $response['bic'] ?? '',
            'COUNTRY' => $response['country'] ?? '',
            'HPP_CUSTOMER_EMAIL' => $response['customeremail'] ?? '',
            'TRANSACTION_STATUS' => $response['fundsstatus'] ?? '',
            'IBAN' => $response['iban'] ?? '',
            'MESSAGE' => $response['message'] ?? '',
            'ORDER_ID' => $response['orderid'] ?? '',
            'PASREF' => $response['pasref'] ?? '',
            'PAYMENTMETHOD' => $response['paymentmethod'] ?? '',
            'PAYMENT_PURPOSE' => $response['paymentpurpose'] ?? '',
            'RESULT' => $response['result'] ?? '',
            $this->shaHashType . "HASH" => $response[strtolower($this->shaHashType).'hash']
        ];
    }

    public function parseResponse($response, $encoded = false)
    {
        if (empty($response)) {
            throw new ApiException("Enable to parse : empty response");
        }
        $response = json_decode($response, true);

        if ($encoded) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($response));
            foreach ($iterator as $key => $value) {
                if (!empty($value)) {
                    $iterator->getInnerIterator()->offsetSet($key, base64_decode($value));
                }
            }

            $response = $iterator->getArrayCopy();
        }
        $isApm = isset($response['paymentmethod']) || isset($response['PAYMENTMETHOD']);
        if (isset($response['fundsstatus'])) {
            $response = $this->mapTransactionStatusResponse($response);
        }

        $timestamp = $response["TIMESTAMP"];
        $merchantId = $response["MERCHANT_ID"];
        $orderId = $response["ORDER_ID"];
        $result = $response["RESULT"];
        $message = $response["MESSAGE"];
        $transactionId = $response["PASREF"];
        $authCode = $response["AUTHCODE"] ?? "";
        $paymentMethod = $response["PAYMENTMETHOD"] ?? "";

        if (empty($response[$this->shaHashType . "HASH"])) {
            throw new ApiException("SHA hash is missing. Please check your code and the Developers Documentation.");
        }
        $shaHash = $response[$this->shaHashType . "HASH"];

        $hash = GenerationUtils::generateNewHash(
            $this->sharedSecret,
            implode('.', [
                $timestamp,
                $merchantId,
                $orderId,
                $result,
                $message,
                $transactionId,
                isset($response['TRANSACTION_STATUS']) ? $paymentMethod : $authCode
            ]),
            $this->shaHashType
        );

        if ($hash !== $shaHash) {
            throw new ApiException("Incorrect hash. Please check your code and the Developers Documentation.");
        }

        $ref = new TransactionReference();
        $ref->authCode = $authCode;
        $ref->orderId = $orderId;
        $ref->paymentMethodType = $isApm ? PaymentMethodType::APM : PaymentMethodType::CREDIT;
        $ref->transactionId = $transactionId;

        $trans = new Transaction();

        $trans->authorizedAmount = $response["AMOUNT"] ?? null;

        $trans->cvnResponseCode = $response["CVNRESULT"] ?? null;
        $trans->responseCode = $result;
        $trans->responseMessage = $message;
        $trans->avsResponseCode = $response["AVSPOSTCODERESULT"] ?? null;
        $trans->transactionReference = $ref;
        if (!empty($response['PAYMENTMETHOD'])) {
            $apm = new AlternativePaymentResponse();
            $apm->country = $response['COUNTRY'] ?? '';
            $apm->providerName = $response['PAYMENTMETHOD'];
            $apm->paymentStatus = $response['TRANSACTION_STATUS'] ?? null;
            $apm->reasonCode = $response['PAYMENT_PURPOSE'] ?? null;
            $apm->accountHolderName = $response['ACCOUNT_HOLDER_NAME'] ?? null;
            $trans->alternativePaymentResponse = $apm;
        }

        $trans->responseValues = $response;

        return $trans;
    }
}
