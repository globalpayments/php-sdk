<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\{IRequestLogger, Transaction};
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\{
    GatewayException, 
    UnsupportedTransactionException
};
use GlobalPayments\Api\Gateways\BillPay\Requests\{
    ReversePaymentRequest, 
    UpdateTokenRequest
};
use GlobalPayments\Api\Gateways\BillPay\Responses\{
    ReversalResponse, 
    UpdateTokenResponse
};
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Utils\ElementTree;

class ManagementRequest extends GatewayRequestBase
{
    public function __construct(Credentials $credentials, string $serviceUrl, int $timeout, ?IRequestLogger $requestLogger = null)
    {
        parent::__construct();
        $this->credentials = $credentials;
        $this->serviceUrl = $serviceUrl;
        $this->timeout = $timeout;
        $this->requestLogger = $requestLogger;
    }

    public function execute(ManagementBuilder $builder, bool $isBillDataHosted): Transaction
    {
        switch ($builder->transactionType) {
            case TransactionType::REFUND:
            case TransactionType::REVERSAL:
            case TransactionType::VOID:
                return $this->reversePayment($builder);
            case TransactionType::TOKEN_UPDATE:
                if ($builder->paymentMethod instanceof CreditCardData) {
                    $card = $builder->paymentMethod;
                    return $this->updateTokenExpirationDate($card);
                }

                throw new UnsupportedTransactionException();
            default:
                throw new UnsupportedTransactionException();
        }
    }

    private function reversePayment(ManagementBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "ReversePayment");
        $reversePaymentRequest = new ReversePaymentRequest($et);
        $request = $reversePaymentRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );
        
        /** @var string */
        $response = $this->doTransaction($request);
        $reversalResponse = new ReversalResponse();

        /** @var Transaction */
        $result = $reversalResponse
            ->withResponseTagName("ReversePaymentResponse")
            ->withResponse($response)
            ->map();
        
        if ($result->responseCode == "0") {
            return $result;
        }

        throw new GatewayException(
            "There was an error attempting to reverse the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function updateTokenExpirationDate(CreditCardData $card): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "UpdateTokenExpirationDate");
        $updateTokenRequest = new UpdateTokenRequest($et);
        $request = $updateTokenRequest->build(
            $envelope,
            $card,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $updateTokenResponse = new UpdateTokenResponse();

        /** @var Transaction */
        $result = $updateTokenResponse
            ->withResponseTagName("UpdateTokenExpirationDateResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode == "0") {
            return $result;
        }
        
        throw new GatewayException(
            "There was an error attempting to the token expiry information", 
            $result->responseCode, 
            $result->responseMessage
        );
    }
}