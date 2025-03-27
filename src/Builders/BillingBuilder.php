<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\{BillingLoadType,
    TransactionModifier
};

use GlobalPayments\Api\PaymentMethods\{EBTCardData, CreditCardData};
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\Entities\BillPay\BillingResponse;

class BillingBuilder extends TransactionBuilder
{
    /**
     * Request Bills
     * 
     * @var array<Bill>
     */
    private $bills = array();

    /**
     * Request BillingLoadType
     * 
     * @var BillingLoadType
     */
    private $billingLoadType;

    /**
     * Request HostedPaymentData
     * 
     * @var ?HostedPaymentData
     */
    private $hostedPaymentData;

    /**
     * Request orderId
     *
     * @var string
     */
    private $orderId;

    /**
     * Request commit bills
     *
     * @var boolean
     */
    private $commitBills;

    /**
     * Request clear bills
     *
     * @var boolean
     */
    private $clearBills;

    /**
     * Request customer Data
     *
     * @var Customer
     */
    private $customer;

    /**
     * Request amount
     *
     * @var string|float
     */
    private $amount;

    /**
     * {@inheritdoc}
     *
     * @param TransactionType $type Request transaction type
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return
     */
    // CHECK
    public function __construct($type, IPaymentMethod $paymentMethod = null)
    {
        parent::__construct($type, $paymentMethod);
        $this->withPaymentMethod($paymentMethod);

        $this->billingLoadType =  BillingLoadType::NONE;
    }

    /**
     * Set the request payment method
     *
     * @param IPaymentMethod $paymentMethod Request payment method
     *
     * @return BillingBuilder
     */
    public function withPaymentMethod(IPaymentMethod $paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;

        if ($paymentMethod instanceof EBTCardData && $paymentMethod->serialNumber !== null) {
            $this->transactionModifier = TransactionModifier::VOUCHER;
        }

        if ($paymentMethod instanceof CreditCardData && $paymentMethod->mobileType !== null) {
            $this->transactionModifier = TransactionModifier::ENCRYPTED_MOBILE;
        }

        return $this;
    }

    public function getBills() 
    {
        return $this->bills;
    }

    public function getBillingLoadType() 
    {
        return $this->billingLoadType;
    }

    public function getHostedPaymentData() 
    {
        return $this->hostedPaymentData;
    }

    public function getOrderId() 
    {
        return $this->orderId;
    }

    public function getCommitBills() 
    {
        return $this->commitBills;
    }

    public function getClearBills() 
    {
        return $this->clearBills;
    }

    public function getCustomer() 
    {
        return $this->customer;
    }

    public function getAmount() 
    {
        return $this->amount;
    }

    public function withOrderId(string $orderId) 
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function commitPreloadedBills() 
    {
        $this->commitBills = true;

        return $this;
    }

    public function clearPreloadedBills() 
    {
        $this->clearBills = true;

        return $this;
    }

    /**
     * @param BillingLoadType $billingLoadType 
     */
    public function withBillingLoadType($billingLoadType): BillingBuilder
    {
        $this->billingLoadType = $billingLoadType;

        return $this;
    }

    public function withBills($bills): BillingBuilder
    {
        $this->bills = $bills;

        return $this;
    }

    public function withHostedPaymentData(HostedPaymentData $hostedPaymentData): BillingBuilder
    {
        $this->hostedPaymentData = $hostedPaymentData;
        return $this;
    }

    public function withCustomer(Customer $customer): BillingBuilder
    {
        $this->customer = $customer;

        return $this;
    }

    public function withAmount($amount): BillingBuilder
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return BillingResponse
     */
    public function execute($configName = 'default')
    {
        parent::execute($configName);

        $client = ServicesContainer::instance()->getBillingClient($configName);
        return $client->processBillingRequest($this);
    }

    protected function setupValidations()
    {
        // Intended to be unimplemented, same implementation on other SDKs
    }

}  