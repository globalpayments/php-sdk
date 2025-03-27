<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\{IRequestLogger, Transaction};
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Enums\{PaymentMethodUsageMode, TransactionType};
use GlobalPayments\Api\Entities\Exceptions\{BuilderException, GatewayException, UnsupportedTransactionException};
use GlobalPayments\Api\Gateways\BillPay\Requests\{
    GetACHTokenRequest, GetTokenInformationRequest, GetTokenRequest, MakeBlindPaymentRequest,
    MakeBlindPaymentReturnTokenRequest, MakePaymentRequest, MakePaymentReturnTokenRequest, MakeQuickPayBlindPaymentRequest,
    MakeQuickPayBlindPaymentReturnTokenRequest};
use GlobalPayments\Api\Gateways\BillPay\Responses\{TokenInformationRequestResponse, TokenRequestResponse, TransactionResponse};
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\Utils\ElementTree;

class AuthorizationRequest extends GatewayRequestBase
{
    public function __construct(Credentials $credentials, string $serviceUrl, int $timeout, ?IRequestLogger $requestLogger = null)
    {
        parent::__construct();
        $this->credentials = $credentials;
        $this->serviceUrl = $serviceUrl;
        $this->timeout = $timeout;
        $this->requestLogger = $requestLogger;
    }

    /**
     * 
     * @param AuthorizationBuilder $builder 
     * @param bool $isBillDataHosted 
     * @return Transaction 
     * @throws DOMException 
     * @throws UnsupportedTransactionException 
     * @throws BuilderException 
     * @throws GatewayException 
     */
    public function execute(AuthorizationBuilder $builder, bool $isBillDataHosted)
    {
        switch ($builder->transactionType)
        {
            case TransactionType::SALE:
                if ($isBillDataHosted) {
                    if ($builder->requestMultiUseToken) {
                        return $this->makePaymentReturnToken($builder);
                    }

                    return $this->makePayment($builder);
                }

                if ($builder->requestMultiUseToken) {
                    return ($builder->paymentMethodUsageMode !== null && $builder->paymentMethodUsageMode === PaymentMethodUsageMode::SINGLE) ? $this->makeQuickPayBlindPaymentReturnToken($builder) : $this->makeBlindPaymentReturnToken($builder);
                }

                return ($builder->paymentMethodUsageMode != null &&
                        $builder->paymentMethodUsageMode === PaymentMethodUsageMode::SINGLE) ? $this->makeQuickPayBlindPayment($builder) : $this->makeBlindPayment($builder);
            case TransactionType::VERIFY:
                if (!$builder->requestMultiUseToken) {
                    throw new UnsupportedTransactionException();
                }

                if ($builder->paymentMethod instanceof ECheck) {
                    return $this->getACHToken($builder);
                }

                return $this->getToken($builder);
            case TransactionType::GET_TOKEN_INFO:
                return $this->getTokenInformation($builder);
            default:
                throw new UnsupportedTransactionException();
        }
    }

    private function makePaymentReturnToken(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakePaymentReturnToken");
        $makePaymentReturnTokenRequest = new MakePaymentReturnTokenRequest($et);
        $request = $makePaymentReturnTokenRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakePaymentReturnTokenResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function makeBlindPaymentReturnToken(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakeBlindPaymentReturnToken");
        $makeBlindPaymentReturnTokenRequest = new MakeBlindPaymentReturnTokenRequest($et);
        $request = $makeBlindPaymentReturnTokenRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakeBlindPaymentReturnTokenResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function makeBlindPayment(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakeBlindPayment");
        $makeBlindPaymentRequest = new MakeBlindPaymentRequest($et);
        $request = $makeBlindPaymentRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakeBlindPaymentResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function makePayment(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakePayment");
        $makePaymentRequest = new MakePaymentRequest($et);
        $request = $makePaymentRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakePaymentResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function getToken(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "GetToken");
        $getTokenRequest = new GetTokenRequest($et);
        $request = $getTokenRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $tokenRequestResponse = new TokenRequestResponse();

        /** @var Transaction */
        $result = $tokenRequestResponse
            ->withResponseTagName("GetTokenResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to create the token", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function getACHToken(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "GetToken");
        $getACHTokenRequest = new GetACHTokenRequest($et);
        $request = $getACHTokenRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $tokenRequestResponse = new TokenRequestResponse();

        /** @var Transaction */
        $result = $tokenRequestResponse
            ->withResponseTagName("GetTokenResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to create the token", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function getTokenInformation(AuthorizationBuilder $builder): Transaction
    {
        /** @var string */
        $request = null;
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "GetTokenInformation");

        if ($builder->paymentMethod instanceof ITokenizable) {
            if ($builder->paymentMethod->token === null || $builder->paymentMethod->token === "") {
                throw new BuilderException("Payment method has not been tokenized");
            }
            $getTokenInformationRequest = new GetTokenInformationRequest($et);
            $request = $getTokenInformationRequest->build($envelope, $builder, $this->credentials);
        } else {
            throw new BuilderException("Token Information is currently only retrievable for Credit and eCheck payment methods.");
        }

        /** @var string */
        $response = $this->doTransaction($request);
        $tokenInformationRequestResponse = new TokenInformationRequestResponse();

        /** @var Transaction */
        $result = $tokenInformationRequestResponse
            ->withResponseTagName("GetTokenInformationResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException("message: An error occurred attempting to retrieve token information. ResponseCode: " . $result->responseCode . " responseMessage: " . $result->responseMessage);
    }

    private function makeQuickPayBlindPayment(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakeQuickPayBlindPayment");
        $makeQuickPayBlindPaymentRequest = new MakeQuickPayBlindPaymentRequest($et);
        $request = $makeQuickPayBlindPaymentRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakeQuickPayBlindPaymentResponse")
            ->withResponse($response)
            ->map();

        if ($result->responseCode === "0") {
            return $result;
        }
    
        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }

    private function makeQuickPayBlindPaymentReturnToken(AuthorizationBuilder $builder): Transaction
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "MakeQuickPayBlindPaymentReturnToken");
        $makeQuickPayBlindPaymentReturnToken = new MakeQuickPayBlindPaymentReturnTokenRequest($et);
        $request = $makeQuickPayBlindPaymentReturnToken->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $transactionResponse = new TransactionResponse();

        /** @var Transaction */
        $result = $transactionResponse
            ->withResponseTagName("MakeQuickPayBlindPaymentReturnTokenResponse")
            ->withResponse($response)
            ->map();
        
        if ($result->responseCode === "0") {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to make the payment", 
            $result->responseCode, 
            $result->responseMessage
        );
    }
}