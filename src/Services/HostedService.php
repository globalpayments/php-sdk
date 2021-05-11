<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\ServiceConfigs\ServicesConfig;

class HostedService
{

    /**
     * Shared secret to authenticate with the gateway
     *
     * @var string
     */
    public $sharedSecret;

    /**
     * Instatiates a new object
     *
     * @param ServicesConfig $config Service config
     *
     * @return void
     */
    public function __construct($config)
    {
        ServicesContainer::configureService($config);
        $this->sharedSecret = $config->sharedSecret;
    }

    /**
     * Creates an authorization builder with type
     * `TransactionType::CREDIT_AUTH`
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function authorize($amount = null)
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

    public function parseResponse($response, $encoded = false)
    {
        $response = json_decode($response, true);

        if ($encoded) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($response));
            foreach ($iterator as $key => $value) {
                $iterator->getInnerIterator()->offsetSet($key, base64_decode($value));
            }

            $response = $iterator->getArrayCopy();
        }

        $timestamp = $response["TIMESTAMP"];
        $merchantId = $response["MERCHANT_ID"];
        $orderId = $response["ORDER_ID"];
        $result = $response["RESULT"];
        $message = $response["MESSAGE"];
        $transactionId = $response["PASREF"];
        $authCode = $response["AUTHCODE"];
        $sha1Hash = $response["SHA1HASH"];
        $hash = GenerationUtils::generateHash($this->sharedSecret, implode('.', [
                    $timestamp,
                    $merchantId,
                    $orderId,
                    $result,
                    $message,
                    $transactionId,
                    $authCode
        ]));

        if ($hash != $sha1Hash) {
            throw new ApiException("Incorrect hash. Please check your code and the Developers Documentation.");
        }

        $ref = new TransactionReference();
        $ref->authCode = $authCode;
        $ref->orderId = $orderId;
        $ref->paymentMethodType = PaymentMethodType::CREDIT;
        $ref->transactionId = $transactionId;

        $trans = new Transaction();

        if (isset($response["AMOUNT"])) {
            $trans->authorizedAmount = $response["AMOUNT"];
        }
        
        $trans->cvnResponseCode = $response["CVNRESULT"];
        $trans->responseCode = $result;
        $trans->responseMessage = $message;
        $trans->avsResponseCode = $response["AVSPOSTCODERESULT"];
        $trans->transactionReference = $ref;
        
        $trans->responseValues = $response;

        return $trans;
    }
}
