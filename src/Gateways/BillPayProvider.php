<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\{
    AuthorizationBuilder,
    ManagementBuilder,
    RecurringBuilder,
    ReportBuilder,
    BillingBuilder
};
use GlobalPayments\Api\Entities\{IRequestLogger, Transaction};
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Gateways\Interfaces\IBillingProvider;
use GlobalPayments\Api\Gateways\{IPaymentGateway, IRecurringService};
use GlobalPayments\Api\Entities\BillPay\{BillingResponse, Credentials};
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Gateways\BillPay\{
    AuthorizationRequest,
    BillingRequest,
    ManagementRequest,
    RecurringRequest,
    ReportRequest
};

class BillPayProvider implements IBillingProvider, IPaymentGateway, IRecurringService
{
    private Credentials $credentials;
    
    public bool $isBillDataHosted;
    public int $timeout;
    public string $serviceUrl;
    public ?IRequestLogger $requestLogger;


    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    public function isBillDataHosted(): bool
    {
        return $this->isBillDataHosted;
    }

    public function setIsBillDataHosted(bool $isBillDataHosted)
    {
        $this->isBillDataHosted = $isBillDataHosted;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
    
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function getServiceUrl(): string
    {
        return $this->serviceUrl;
    }

    public function setServiceUrl(string $serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
    }

    /**
     * Invokes a request against the BillPay gateway using the AuthorizationBuilder
     * 
     * @param AuthorizationBuilder $builder The transaction's builder
     * 
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder): Transaction
    {
        $authorizationRequest = new AuthorizationRequest(
            $this->credentials, 
            $this->serviceUrl, 
            $this->timeout, 
            $this->requestLogger
        );
        
        return $authorizationRequest->execute($builder, $this->isBillDataHosted);
    }

    /**
     * Invokes a request against the BillPay gateway using the ManagementBuilder
     *
     * @param ManagementBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function manageTransaction(ManagementBuilder $builder): Transaction
    {
        $managementRequest = new ManagementRequest(
            $this->credentials,
            $this->serviceUrl,
            $this->timeout,
            $this->requestLogger
        );

        return $managementRequest->execute(
            $builder,
            $this->isBillDataHosted()
        );
    }

    /**
     * Invokes a request against the BillPay gateway using the ManagementBuilder
     *
     * @param BillingBuilder $builder The transaction's builder
     *
     * @return BillingResponse
     */
    public function processBillingRequest(BillingBuilder $builder): BillingResponse
    {
        $billingRequest = new BillingRequest(
            $this->credentials, 
            $this->serviceUrl, 
            $this->timeout,
            $this->requestLogger,
        );

        return $billingRequest->execute($builder);
    }

    public function supportsOpenBanking() : bool
    {
        return false;
    }

    public function supportsHostedPayments(): bool
    {
        return true;
    }

    public function supportsRetrieval() : bool
    {
        return false;
    }

    public function supportsUpdatePaymentDetails(): bool
    {
        return false;
    }

    /**
     * Invokes a request against the BillPay gateway using the ReportBuilder
     *
     * @param ReportBuilder $builder The transaction's builder
     *
     * @return TransactionSummary
     */
    public function processReport(ReportBuilder $builder): TransactionSummary
    {
        $reportRequest = new ReportRequest(
            $this->credentials, 
            $this->serviceUrl, 
            $this->timeout,
            $this->requestLogger,
        );

        return $reportRequest->execute($builder);
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        $recurringRequest = new RecurringRequest(
            $this->credentials, 
            $this->serviceUrl, 
            $this->timeout,
            $this->requestLogger
        );

        return $recurringRequest->execute($builder);
    }
}