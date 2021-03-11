<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData;

use GlobalPayments\Api\Entities\PayFac\FlashFundsPaymentCardData;

class TestFundsData
{
    public static function getFlashFundsData()
    {
        $flashFundsData = new FlashFundsPaymentCardData();
        
        $flashFundsData->creditCard->number = '4895142232120006';
        $flashFundsData->creditCard->expMonth = 10;
        $flashFundsData->creditCard->expYear = 2025;
        $flashFundsData->creditCard->cvn = '022';
        $flashFundsData->creditCard->cardHolderName = 'Clint Eastwood';
        
        $flashFundsData->cardholderAddress->streetAddress1 = '900 Metro Center Blv';
        $flashFundsData->cardholderAddress->city = 'San Fransisco';
        $flashFundsData->cardholderAddress->state = 'CA';
        $flashFundsData->cardholderAddress->postalCode = '94404';
        $flashFundsData->cardholderAddress->country = 'USA';
        $flashFundsData->cardholderAddress->phone = '12233445';
        
        return $flashFundsData;
    }
}
