<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\BillingBuilder;
use GlobalPayments\Api\Entities\BillPay\{ConvenienceFeeResponse, LoadSecurePayResponse};
use GlobalPayments\Api\Entities\Enums\{BillingLoadType, TransactionType};
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class BillPayService
{
    /**
     * Returns the fee for the given payment method and amount
     * 
     * @param IPaymentMethod $paymentMethod The payment method that will be used to make the charge against
     * @param float $amount The total amount to be charged
     * @return float
     */
    public function calculateConvenienceAmount(IPaymentMethod $paymentMethod, float $amount): float
    {
        return $this->calculateConvenienceFee($paymentMethod, $amount, "default");
    }

    public function calculateConvenienceFee(IPaymentMethod $paymentMethod, float $amount, string $configName): float
    {
        $billingBuilder = new BillingBuilder(TransactionType::FETCH);
        
        $response = $billingBuilder->withPaymentMethod($paymentMethod)
            ->withAmount($amount)
            ->execute();

        /** @var ConvenienceFeeResponse $convenienceFeeResponse */
        $convenienceFeeResponse = $response;

        return $convenienceFeeResponse->convenienceFee;
    }

    /**
     * Loads one or more bills for a specific customer and returns an identifier that can be used by the customer to retrieve their bills
     * 
     * @param HostedPaymentData $hostedPaymentData The payment data to be hosted
     * @param string $configName
     * @return LoadSecurePayResponse The name of the registered configuration to retrieve. This defaults to 'default'
     */
    public function loadHostedPayment(HostedPaymentData $hostedPaymentData, string $configName = 'default'): LoadSecurePayResponse
    {
        $billingBuilder = new BillingBuilder(TransactionType::CREATE);
        $response = $billingBuilder->withBillingLoadType(BillingLoadType::SECURE_PAYMENT)
            ->withHostedPaymentData($hostedPaymentData)
            ->execute($configName);
            
        /** @var LoadSecurePayResponse $result */
        $result = $response;

        return $result;
    }

    /**
     * Loads one or more bills for one or many customers
     * 
     * @param array<Bill> $bills The collection of bills to load
     * @param string $configName The name of the registered configuration to retrieve. This defaults to 'default'
     */
    public function loadBills(array $bills, string $configName = "default")
    {
        $maxBillsPerUpload = 1000;
        $billCount = count($bills);
        $numberOfCalls = $billCount < $maxBillsPerUpload ? 1 : (int) ($billCount / $maxBillsPerUpload);

        for ($i = 0; $i < $numberOfCalls; $i++)
        {
            // skipped bills from previous uploads
            $fromIndex = $i * $maxBillsPerUpload;
            // limit bills to `maxBillsPerUpload`
            $toIndex = $fromIndex + ($billCount < $maxBillsPerUpload ? $billCount : $maxBillsPerUpload);

            /** @var array<Bill> */
            $currentSetOfBills = $this->getSublist($bills, $fromIndex, $toIndex);

            $billingBuilder = new BillingBuilder(TransactionType::CREATE);
            $response = $billingBuilder->withBillingLoadType(BillingLoadType::BILLS)
                ->withBills($currentSetOfBills)
                ->execute($configName);
        }
 
    }

    /**
     * Removes all bills that have been loaded and have not been committed
     * 
     * @param string $configName The name of the registered configuration to retrieve. This defaults to 'default'
     */
    public function clearBills(string $configName = "default")
    {
        $billingBuilder = new BillingBuilder(TransactionType::DELETE);
        
        return $billingBuilder->withBillingLoadType(BillingLoadType::BILLS)
            ->clearPreloadedBills()
            ->execute();
    }

    /**
     * Commits all bills that have been preloaded
     * 
     * @param string $configName The name of the registered configuration to retrieve. This defaults to 'default'
     */
    public function commitPreloadedBills(string $configName = "default") 
    {
        $billingBuilder = new BillingBuilder(TransactionType::ACTIVATE);

        return $billingBuilder->withBillingLoadType(BillingLoadType::BILLS)
            ->commitPreloadedBills()
            ->execute();
    }

    private function getSublist(array $array, int $start, int $length): array {
        $sublist = [];

        for ($i = $start; $i < $length; $i++) {
            $sublist[] = $array[$i];
        }
    
        return $sublist;
    }
    
}