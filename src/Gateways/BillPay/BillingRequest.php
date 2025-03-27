<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Entities\IRequestLogger;
use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\{
    BillingResponse,
    ConvenienceFeeResponse,
    Credentials,
    LoadSecurePayResponse
};
use GlobalPayments\Api\Gateways\BillPay\Requests\{
    ClearLoadedBillsRequest,
    CommitPreloadedBillsRequest,
    GetConvenienceFeeRequest,
    LoadSecurePayRequest,
    PreloadBillsRequest
};
use GlobalPayments\Api\Gateways\BillPay\Responses\{
    BillingRequestResponse,
    ConvenienceFeeRequestResponse,
    PreloadBillsResponse,
    SecurePayResponse
};
use GlobalPayments\Api\Entities\Enums\{BillingLoadType, TransactionType};
use GlobalPayments\Api\Entities\Exceptions\{GatewayException, UnsupportedTransactionException};
use GlobalPayments\Api\Utils\ElementTree;

class BillingRequest extends GatewayRequestBase
{
    public function __construct(Credentials $credentials, string $serviceUrl, int $timeout, ?IRequestLogger $requestLogger = null)
    {
        parent::__construct();
        $this->credentials = $credentials;
        $this->serviceUrl = $serviceUrl;
        $this->timeout = $timeout;
        $this->requestLogger = $requestLogger;
    }

    public function execute(BillingBuilder $builder): BillingResponse
    {
        switch ($builder->transactionType) 
        {
            case TransactionType::ACTIVATE:
                return $this->commitPreloadBills();
            case TransactionType::CREATE:
                if ($builder->getBillingLoadType() == BillingLoadType::BILLS)
                {
                    return $this->preloadBills($builder);
                }

                if ($builder->getBillingLoadType() == BillingLoadType::SECURE_PAYMENT)
                {
                    return $this->loadSecurePay($builder);
                }

                throw new UnsupportedTransactionException();
            case TransactionType::FETCH:
                return $this->getConvenienceFee($builder);
            case TransactionType::DELETE:
                return $this->clearLoadedBills();
            default:
                throw new UnsupportedTransactionException();
        }
    }

    private function getConvenienceFee(BillingBuilder $builder): ConvenienceFeeResponse
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "GetConvenienceFee");
        $getConvenienceFeeRequest = new GetConvenienceFeeRequest($et);
        $request = $getConvenienceFeeRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $convenienceFeeRequestResponse = new ConvenienceFeeRequestResponse();

        /** @var ConvenienceFeeResponse */
        $result = $convenienceFeeRequestResponse
            ->withResponseTagName("GetConvenienceFeeResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to create the token", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function preloadBills(BillingBuilder $builder): BillingResponse
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "PreloadBills");
        $preloadBillsRequest = new PreloadBillsRequest($et);
        $request = $preloadBillsRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $preloadBillsResponse = new PreloadBillsResponse();

        /** @var BillingResponse */
        $result = $preloadBillsResponse
            ->withResponseTagName("PreloadBillsResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $result;
        }
    
        throw new GatewayException(
            "An error occurred attempting to load the hosted bills", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function commitPreloadBills(): BillingResponse
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "CommitPreloadedBills");
        $commitPreloadedBillsRequest = new CommitPreloadedBillsRequest($et);
        $request = $commitPreloadedBillsRequest->build(
            $envelope,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $billingRequestResponse = new BillingRequestResponse();

        /** @var BillingResponse */
        $result = $billingRequestResponse
            ->withResponseTagName("CommitPreloadedBillsResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $result;
        }
        
        throw new GatewayException(
            "An error occurred attempting to commit the preloaded bills", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function clearLoadedBills(): BillingResponse
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "ClearLoadedBills");
        $clearLoadedBillsRequest = new ClearLoadedBillsRequest($et);
        $request = $clearLoadedBillsRequest->build(
            $envelope,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);

        $billingRequestResponse = new BillingRequestResponse();
        
        return $billingRequestResponse
            ->withResponseTagName("ClearLoadedBillsResponse")
            ->withResponse($response)
            ->map();
    }

    private function loadSecurePay(BillingBuilder $builder): LoadSecurePayResponse
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "LoadSecurePayDataExtended");
        $loadSecurePayRequest = new LoadSecurePayRequest($et);
        $request = $loadSecurePayRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $securePayResponse = new SecurePayResponse();

        /** @var LoadSecurePayResponse */
        $result = $securePayResponse
            ->withResponseTagName("LoadSecurePayDataExtendedResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $result;
        }

        throw new GatewayException(
            "An error occurred attempting to load the hosted bill", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }
}