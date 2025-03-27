<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Entities\{Customer, IRequestLogger, Schedule};
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\{GatewayException, UnsupportedTransactionException};
use GlobalPayments\Api\Entities\BillPay\TokenResponse;
use GlobalPayments\Api\Gateways\BillPay\Requests\{
    CreateCustomerAccountRequest,
    CreateRecurringPaymentRequest,
    CreateSingleSignOnAccountRequest,
    DeleteCustomerAccountRequest,
    DeleteSingleSignOnAccountRequest,
    UpdateCustomerAccountRequest,
    UpdateSingleSignOnAccountRequest
};
use GlobalPayments\Api\Gateways\BillPay\Responses\{
    BillingRequestResponse,
    CreateCustomerAccountResponse,
    CustomerAccountResponse,
    SingleSignOnAccountResponse
};
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Utils\ElementTree;

class RecurringRequest extends GatewayRequestBase
{
    public function __construct(Credentials $credentials, string $serviceUrl, int $timeout, ?IRequestLogger $requestLogger = null)
    {
        parent::__construct();
        $this->credentials = $credentials;
        $this->serviceUrl = $serviceUrl;
        $this->timeout = $timeout;
        $this->requestLogger = $requestLogger;
    }

    public function execute(RecurringBuilder $builder)
    {
        if ($builder->entity instanceof Customer) {
            $customer = $builder->entity;

            return $this->customerRequest($customer, $builder->transactionType);
        }

        if ($builder->entity instanceof RecurringPaymentMethod) {
            $recurringPaymentMethod = $builder->entity;
            return $this->customerAccountRequest($recurringPaymentMethod, $builder->transactionType);
        }

        if ($builder->entity instanceof Schedule) {
            return $this->createRecurringPayment($builder->entity);
        }

        throw new UnsupportedTransactionException();
    }

    private function customerRequest(Customer $customer, int $transactionType)
    {
        switch ($transactionType) {
            case TransactionType::CREATE:
                return $this->createSingleSignOnAccount($customer);
            case TransactionType::EDIT:
                return $this->updateSingleSignOnAccount($customer);
            case TransactionType::DELETE:
                return $this->deleteSingleSignOnAccount($customer);
            default:
                throw new UnsupportedTransactionException();
        }
    }

    private function customerAccountRequest(RecurringPaymentMethod $paymentMethod, int $transactionType)
    {
        switch ($transactionType)
        {
            case TransactionType::CREATE:
                return $this->createCustomerAccount($paymentMethod);
            case TransactionType::EDIT:
                return $this->updateCustomerAccount($paymentMethod);
            case TransactionType::DELETE:
                return $this->deleteCustomerAccount($paymentMethod);
            default:
                throw new UnsupportedTransactionException();
        }
    }

    private function createSingleSignOnAccount(Customer $customer)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "CreateSingleSignOnAccount");
        $createSingleSignOnAccountRequest = new CreateSingleSignOnAccountRequest($et);
        $request = $createSingleSignOnAccountRequest->build(
            $envelope,
            $this->credentials,
            $customer
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $singleSignOnAccountResponse = new SingleSignOnAccountResponse();

        /** @var BillingResponse */
        $result = $singleSignOnAccountResponse
            ->withResponseTagName("CreateSingleSignOnAccountResponse")
            ->withResponse($response)
            ->map();
        
        if ($result->isSuccessful()) {
            $customer->key = $customer->id;
            return $customer;
        }

        throw new GatewayException(
            "An error occurred while creating the customer", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function updateSingleSignOnAccount(Customer $customer)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "UpdateSingleSignOnAccount");
        $updateSingleSignOnAccountRequest = new UpdateSingleSignOnAccountRequest($et);
        $request = $updateSingleSignOnAccountRequest->build(
            $envelope,
            $this->credentials,
            $customer
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $singleSignOnAccountResponse = new SingleSignOnAccountResponse();

        /** @var BillingResponse */
        $result = $singleSignOnAccountResponse
            ->withResponseTagName("UpdateSingleSignOnAccountResponse")
            ->withResponse($response)
            ->map();
        
        if ($result->isSuccessful()) {
            return $customer;
        }

        throw new GatewayException(
            "An error occurred while updating the customer", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function deleteSingleSignOnAccount(Customer $customer)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "DeleteSingleSignOnAccount");
        $deleteSingleSignOnAccountRequest = new DeleteSingleSignOnAccountRequest($et);
        $request = $deleteSingleSignOnAccountRequest->build(
            $envelope,
            $this->credentials,
            $customer
        );
        
        /** @var string */
        $response = $this->doTransaction($request);
        $singleSignOnAccountResponse = new SingleSignOnAccountResponse();

        /** @var BillingResponse */
        $result = $singleSignOnAccountResponse
            ->withResponseTagName("DeleteSingleSignOnAccountResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $customer;
        }
        
        throw new GatewayException(
            "An error occurred while deleting the customer", 
            $result->getResponseMessage(), 
            $result->getResponseMessage()
        );
    }

    private function createCustomerAccount(RecurringPaymentMethod $paymentMethod) 
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "SaveCustomerAccount");
        $createCustomerAccountRequest = new CreateCustomerAccountRequest($et);
        $request = $createCustomerAccountRequest->build(
            $envelope,
            $this->credentials,
            $paymentMethod
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $createCustomerAccountResponse = new CreateCustomerAccountResponse();

        /** @var TokenResponse */
        $result = $createCustomerAccountResponse
            ->withResponseTagName("SaveCustomerAccountResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            $paymentMethod->key = $paymentMethod->id;
            $paymentMethod->token = $result->getToken();
            return $paymentMethod;
        }
         
        throw new GatewayException(
            "An error occurred while creating the customer account", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function updateCustomerAccount(RecurringPaymentMethod $paymentMethod)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "UpdateCustomerAccount");
        $updateCustomerAccountRequest = new UpdateCustomerAccountRequest($et);
        $request = $updateCustomerAccountRequest->build(
            $envelope,
            $this->credentials,
            $paymentMethod
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $customerAccountResponse = new CustomerAccountResponse();

        /** @var BillingResponse */
        $result = $customerAccountResponse
            ->withResponseTagName("UpdateCustomerAccountResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $paymentMethod;
        }

        throw new GatewayException(
            "An error occurred while updating the customer account", 
            $result->getResponseCode(), 
            $result->getResponseMessage()
        );
    }

    private function deleteCustomerAccount(RecurringPaymentMethod $paymentMethod)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "DeleteCustomerAccount");
        $deleteCustomerAccountRequest = new DeleteCustomerAccountRequest($et);
        $request = $deleteCustomerAccountRequest->build(
            $envelope,
            $this->credentials,
            $paymentMethod
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $singleSignOnAccountResponse = new SingleSignOnAccountResponse();

        /** @var BillingResponse */
        $result = $singleSignOnAccountResponse
            ->withResponseTagName("DeleteCustomerAccountResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $paymentMethod;
        }

        throw new GatewayException(
            "An error occurred while deleting the customer account", 
            $result->getResponseMessage(), 
            $result->getResponseMessage()
        );
    }

    private function createRecurringPayment(Schedule $schedule)
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "CreateRecurringPayment");
        $createRecurringPaymentRequest = new CreateRecurringPaymentRequest($et);
        $request = $createRecurringPaymentRequest->build(
            $envelope, 
            $this->credentials, 
            $schedule
        );

        /** @var string */
        $response = $this->doTransaction($request);
        $billingRequestResponse = new BillingRequestResponse();

        /** @var BillingResponse */
        $result = $billingRequestResponse
            ->withResponseTagName("CreateRecurringPaymentResponse")
            ->withResponse($response)
            ->map();

        if ($result->isSuccessful()) {
            return $schedule;
        }

        throw new GatewayException(
            "An error occurred while creating the recurring payment", 
            $result->getResponseMessage(), 
            $result->getResponseMessage()
        );
    }
}