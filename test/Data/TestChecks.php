<?php

namespace GlobalPayments\Api\Tests\Data;

use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;

class TestChecks
{
    public static function certification(
        $secCode = SecCode::PPD,
        $checkType = CheckType::PERSONAL,
        $accountType = AccountType::CHECKING,
        $checkHolderName = null
    ) {
        $check = new ECheck();
        $check->accountNumber = '1357902468';
        $check->routingNumber = '122000030';
        $check->checkType = $checkType;
        $check->accountType = $accountType;
        $check->secCode = $secCode;
        $check->entryMode = EntryMethod::MANUAL;
        $check->checkHolderName = 'John Doe';
        $check->driversLicenseNumber = '09876543210';
        $check->driversLicenseState = 'TX';
        $check->phoneNumber = '8003214567';
        $check->birthYear = '1997';
        $check->ssnLast4 = '4321';
        if (!empty($checkHolderName)) {
            $check->checkHolderName = $checkHolderName;
        }
        return $check;
    }
}
