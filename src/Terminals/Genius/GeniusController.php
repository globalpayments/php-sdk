<?php

namespace GlobalPayments\Api\Terminals\Genius;

use Exception;
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    StoredCredentialInitiator,
    TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Gateways\GatewayResponse;
use GlobalPayments\Api\Terminals\{ConnectionConfig, DeviceController, TerminalResponse};
use GlobalPayments\Api\Terminals\Abstractions\{IDeviceCommInterface, ITerminalReport};
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\Genius\Entities\Enums\{
    HttpMethod,
    MitcRequestType,
    TransactionIdType
};
use GlobalPayments\Api\Terminals\Genius\Interfaces\MitcGateway;
use GlobalPayments\Api\Terminals\Genius\Responses\MitcResponse;
use GlobalPayments\Api\Utils\AmountUtils;

class GeniusController extends DeviceController
{
    /**
     * 
     * @var MitcGateway
     */
    public $mitcGateway;

    /** @var GeniusInterface */
    private $device;
    
    public function __construct(ConnectionConfig $config)
    {
        $this->device = new GeniusInterface($this);

        // currently only MitcGateway is supported
        $this->mitcGateway = new MitcGateway($config);
    }

    public function configureConnector(): IDeviceCommInterface
    {
        throw new NotImplementedException();
    }

    public function configureInterface(): IDeviceInterface {
        if ($this->device == null)
            $this->device = new GeniusInterface($this);
        return $this->device;
 }

    /**
     * 
     * @param string $message JSON string containing request contents
     * @param MitcRequestType $requestType
     * @param string $targetId transactionId used for 'Follow-On Transactions'
     * @return GatewayResponse 
     * @throws Exception 
     */
    public function send(
        $message,
        $requestType = null,
        string $targetId = null
    ) : GatewayResponse
    {
        $endpoint = '';
        $verb = '';

        // map endpoint
        switch ($requestType) {
            case MitcRequestType::CARD_PRESENT_SALE:
                $endpoint = '/transactions/cardpresent/sales';
                $verb = HttpMethod::POST;
                $followOnTransaction = false;
                break;
            case MitcRequestType::CARD_PRESENT_REFUND:
                $endpoint = '/transactions/cardpresent/returns';
                $verb = HttpMethod::POST;
                $followOnTransaction = false;
                break;
            case MitcRequestType::REPORT_SALE_CLIENT_ID:
                $endpoint = '/transactions/card/sales/reference_id/' . $targetId;
                $verb = HttpMethod::GET;
                $followOnTransaction = true;
                break;
            case MitcRequestType::REPORT_REFUND_CLIENT_ID:
                $endpoint = '/transactions/card/returns/reference_id/' . $targetId;
                $verb = HttpMethod::GET;
                $followOnTransaction = true;
                break;
            case MitcRequestType::REFUND_BY_CLIENT_ID:
                $endpoint = '/transactions/creditsales/reference_id/' . $targetId . '/creditreturns';
                $verb = HttpMethod::POST;
                $followOnTransaction = true;
                break;
            case MitcRequestType::VOID_CREDIT_SALE:
                $endpoint = '/transactions/creditsales/reference_id/' . $targetId . '/voids';
                $verb = HttpMethod::PUT;
                $followOnTransaction = true;
                break;
            case MitcRequestType::VOID_DEBIT_SALE:
                $endpoint = '/transactions/debitsales/reference_id/' . $targetId . '/voids';
                $verb = HttpMethod::PUT;
                $followOnTransaction = true;
                break;
            case MitcRequestType::VOID_REFUND:
                $endpoint = '/transactions/creditreturns/reference_id/' . $targetId . '/voids';
                $verb = HttpMethod::PUT;
                $followOnTransaction = true;
                break;
        }

        $dynamicHeaders = array();

        if (!$followOnTransaction)
            $dynamicHeaders['X-GP-Target-Device'] = $this->mitcGateway->targetDevice;
        
        $this->device->geniusController->mitcGateway->dynamicHeaders = $dynamicHeaders;
        
        return $this->mitcGateway->send($message, $endpoint, $verb);
    }

    /**
     * 
     * @param TerminalAuthBuilder $builder 
     * @return MitcResponse 
     * @throws Exception 
     */
    public function processTransaction($builder) : TerminalResponse
    {
        $healthcareAmounts = array();

        if (isset($builder->autoSubstantiation)) {
            $autoSubObj = $builder->autoSubstantiation;

            if (0 != $autoSubObj->getCopaySubTotal())
                $healthcareAmounts['copay_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getCopaySubTotal()
                );

            if (0 != $autoSubObj->getClinicSubTotal())
                $healthcareAmounts['clinical_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getClinicSubTotal()
                );

            if (0 != $autoSubObj->getDentalSubTotal())
                $healthcareAmounts['dental_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getDentalSubTotal()
                );

            if (0 != $autoSubObj->getPrescriptionSubTotal())
                $healthcareAmounts['prescription_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getPrescriptionSubTotal()
                );

            if (0 != $autoSubObj->getVisionSubTotal())
                $healthcareAmounts['vision_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getVisionSubTotal()
                );

            if (0 != $autoSubObj->getTotalHealthcareAmount())
                $healthcareAmounts['healthcare_total_amount'] = AmountUtils::transitFormat(
                    $autoSubObj->getTotalHealthcareAmount()
                );
        }

        $purchaseOrder = array();

        if (isset($builder->address) && !empty($builder->address->postalCode))
            $purchaseOrder['destination_postal_code'] = $builder->address->postalCode;

        if (!empty($builder->poNumber))
            $purchaseOrder['po_number'] = $builder->poNumber;

        if (!empty($builder->taxAmount))
            $purchaseOrder['tax_amount'] = AmountUtils::transitFormat(
                $builder->taxAmount
            );

        $payment = array();

        if (!empty($builder->amount))
            $payment['amount'] = AmountUtils::transitFormat($builder->amount);
        
        $payment['currency_code'] = '840'; // may add logic here

        if (!empty($builder->invoiceNumber)) {
            $payment['invoice_number'] = $builder->invoiceNumber;
        } else {
            throw new ApiException(
                'Invoice Number is required for this transaction type'
            );
        }

        if (!empty($builder->gratuity))
            $payment['gratuity_eligible_amount'] = AmountUtils::transitFormat(
                $builder->gratuity
            );

        if (count($healthcareAmounts) > 0)
            $payment['healthcare_amounts'] = $healthcareAmounts;

        if (count($purchaseOrder) > 0)
            $payment['purchase_order'] = $purchaseOrder;

        $receipt = array();

        if (!empty($builder->clerkId)) {
            $receipt['clerk_id'] = $builder->clerkId;
        } else {
            $receipt['clerk_id'] = 'NA';
        }

        $processingIndicators = array();

        if (isset($builder->allowDuplicates))
            $processingIndicators['allow_duplicate'] = $builder->allowDuplicates;

        if (isset($builder->tokenRequest))
            $processingIndicators['create_token'] = $builder->tokenRequest;

        if (isset($builder->allowPartialAuth))
            $processingIndicators['partial_approval'] = $builder->allowPartialAuth;

        $terminal = array();
        $terminal['terminal_id'] = $this->mitcGateway->terminalId;

        $transaction= array();

        if (isset($this->mitcGateway->allowKeyEntry))
            $transaction['keyed_entry_mode'] = 'allowed';

        $transaction['country_code'] = '840'; // may add logic here in the future
        $transaction['language'] = 'en-US'; // and here too

        if (count($processingIndicators) > 0)
            $transaction['processing_indicators'] = $processingIndicators;

        if (isset($builder->tokenRequest) && $builder->tokenRequest) {
            if ($builder->transactionInitiator == StoredCredentialInitiator::CARDHOLDER) {
                $transaction['create_token_reason'] = 'unscheduled_customer_initiated_transaction';
            } else {
                $transaction['create_token_reason'] = 'unscheduled_merchant_initiated_transaction';
            }  
        }

        $transaction['terminal'] = $terminal;

        $request = array();

        if (!empty($builder->clientTransactionId)) {
            $request['reference_id'] = $builder->clientTransactionId;
        } else {
            throw new ApiException(
                'Client transaction ID is required for this transaction type'
            );
        }

        $request['payment'] = $payment;
        $request['receipt'] = $receipt;
        $request['transaction'] = $transaction;

        $requestType = null;
        $targetId = null;

        if ($builder->transactionType == TransactionType::SALE) {
            $requestType = MitcRequestType::CARD_PRESENT_SALE;
        } elseif ($builder->transactionType == TransactionType::REFUND) {
            $requestType = MitcRequestType::CARD_PRESENT_REFUND;
        }

        // send the request
        $gatewayResponse = 
            $this->send(json_encode($request), $requestType, $targetId);

        return new MitcResponse(
            $gatewayResponse->statusCode,
            $gatewayResponse->header,
            json_decode($gatewayResponse->rawResponse, true)
        );
    }

    /**
     * 
     * @param TransactionType $transactionType 
     * @param string $transactionId 
     * @param TransactionIdType $transactionIdType 
     * @return MitcResponse 
     * @throws ApiException 
     * @throws Exception 
     */
    public function processReport(
        TerminalReportBuilder $builder
    ) : ITerminalReport
    {
        if ($builder->searchBuilder->transactionType == TransactionType::SALE) {
            if ($builder->searchBuilder->transactionIdType == TransactionIdType::CLIENT_TRANSACTION_ID) {
                $requestType = MitcRequestType::REPORT_SALE_CLIENT_ID;
            } else {
                $requestType = MitcRequestType::REPORT_SALE_GATEWAY_ID;
            }
        } elseif ($builder->searchBuilder->transactionIdType == TransactionType::REFUND) {
            if ($builder->searchBuilder->transactionIdType == TransactionIdType::CLIENT_TRANSACTION_ID) {
                $requestType = MitcRequestType::REPORT_REFUND_CLIENT_ID;
            } else {
                $requestType = MitcRequestType::REPORT_REFUND_GATEWAY_ID;
            }
        } else {
            throw new ApiException(
                'Target transaction type must be either a sale or refund'
            );
        }

        // send the request
        $gatewayResponse = 
            $this->send(null, $requestType, $builder->searchBuilder->transactionId);

        return new MitcResponse(
            $gatewayResponse->statusCode,
            $gatewayResponse->header,
            json_decode($gatewayResponse->rawResponse, true)
        );
    }

    /**
     * 
     * @param MitcManageBuilder $builder 
     * @return MitcResponse 
     */
    public function manageTransaction($builder) : TerminalResponse
    {
        $payment = array();

        if (!empty($builder->amount))
            $payment['amount'] = AmountUtils::transitFormat($builder->amount);

        if (!empty($builder->invoiceNumber))
            $payment['invoice_number'] = $builder->invoiceNumber;

        $receipt = array();

        if (!empty($builder->clerkId)) {
            $receipt['clerk_id'] = $builder->clerkId;
        } else {
            $receipt['clerk_id'] = 'NA';
        }

        $processingIndicators = array();

        if (isset($builder->allowDuplicates))
            $processingIndicators['allow_duplicate'] = $builder->allowDuplicates;

        if (isset($builder->receipt) && $builder->receipt) {
            $processingIndicators['generate_receipt'] = true;
        } else {
            $processingIndicators['generate_receipt'] = false;
        }

        $transaction= array();
        $transaction['processing_indicators'] = $processingIndicators;

        $request = array();

        if (count($payment) > 0)
            $request['payment'] = $payment;

        if (count($transaction) > 0)
            $request['transaction'] = $transaction;

        $requestType = null;
        $targetId = $builder->clientTransactionId;

        if ($builder->followOnTransactionType == TransactionType::VOID) {
            if ($builder->transactionType == TransactionType::REFUND) {
                $requestType = MitcRequestType::VOID_REFUND;
            } else {
                if ($builder->paymentMethodType == PaymentMethodType::CREDIT) {
                    $requestType = MitcRequestType::VOID_CREDIT_SALE;
                } else {
                    $requestType = MitcRequestType::VOID_DEBIT_SALE;
                }
            }            
        } elseif ($builder->followOnTransactionType == TransactionType::REFUND) {
            $requestType = MitcRequestType::REFUND_BY_CLIENT_ID;
        }

        // send the request
        $gatewayResponse = 
            $this->send(json_encode($request), $requestType, $targetId);

        return new MitcResponse(
            $gatewayResponse->statusCode,
            $gatewayResponse->header,
            json_decode($gatewayResponse->rawResponse, true)
        );
    }
}
