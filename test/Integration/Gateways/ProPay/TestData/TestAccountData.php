<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData;

use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\BeneficialOwnerData;
use GlobalPayments\Api\Entities\PayFac\BusinessData;
use GlobalPayments\Api\Entities\PayFac\OwnersData;
use GlobalPayments\Api\Entities\PayFac\SignificantOwnerData;
use GlobalPayments\Api\Entities\PayFac\ThreatRiskData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\PayFac\GrossBillingInformation;
use GlobalPayments\Api\Entities\PayFac\AccountPermissions;
use GlobalPayments\Api\Entities\Enums\ProPayAccountStatus;
use GlobalPayments\Api\Entities\PayFac\RenewAccountData;
use GlobalPayments\Api\Entities\PayFac\{DeviceDetails, DeviceOrder};

class TestAccountData
{
    public static function getBankAccountData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountCountryCode = 'USA';
        $bankAccountInformation->accountName = 'MyBankAccount';
        $bankAccountInformation->accountNumber = '123456789';
        $bankAccountInformation->accountOwnershipType = 'Personal';
        $bankAccountInformation->accountType = 'C';
        $bankAccountInformation->routingNumber = '102000076';
        
        return $bankAccountInformation;
    }
    public static function getBusinessData()
    {
        $userBusinessInformation = new BusinessData();
        $userBusinessInformation->businessLegalName = 'Twain Enterprises';
        $userBusinessInformation->doingBusinessAs = 'Twain Enterprises';
        $userBusinessInformation->employerIdentificationNumber = mt_rand(100000000, 999999999);
        $userBusinessInformation->businessDescription = 'Accounting Services';
        $userBusinessInformation->websiteURL = 'https://www.Propay.com';
        $userBusinessInformation->merchantCategoryCode = '5399';
        $userBusinessInformation->monthlyBankCardVolume = 50000;
        $userBusinessInformation->averageTicket = 100;
        $userBusinessInformation->highestTicket = 300;
        $userBusinessInformation->businessType = 'D';
        $userBusinessInformation->businessAddress->streetAddress1 = '3400 Ashton Blvd';
        $userBusinessInformation->businessAddress->city = 'Lehi';
        $userBusinessInformation->businessAddress->state = 'UT';
        $userBusinessInformation->businessAddress->postalCode = '84045';
        $userBusinessInformation->businessAddress->country = 'USA';

        return $userBusinessInformation;
    }
    public static function getUserPersonalData()
    {
        $accountPersonalInformation = new UserPersonalData();
        $accountPersonalInformation->dayPhone = 4464464464;
        $accountPersonalInformation->eveningPhone = 4464464464;
        $accountPersonalInformation->externalId = uniqid();
        $accountPersonalInformation->firstName = 'David';
        $accountPersonalInformation->lastName = 'Tennant';
        $accountPersonalInformation->phonePin = 1234;
        $accountPersonalInformation->sourceEmail = sprintf("user%s@user.com", mt_rand(1, 10000));
        $accountPersonalInformation->notificationEmail = sprintf("user%s@user.com", mt_rand(1, 10000));
        $accountPersonalInformation->ssn = 123456789;
        $accountPersonalInformation->dateOfBirth = '01-01-1981';
        $accountPersonalInformation->tier = 'TestEIN';
        $accountPersonalInformation->ipSignup = '4.14.150.145';
        $accountPersonalInformation->uSCitizen = true;
        $accountPersonalInformation->bOAttestation = true;
        $accountPersonalInformation->termsAcceptanceIP = '4.14.150.145';
        $accountPersonalInformation->termsAcceptanceTimeStamp = '2022-10-27 12:57:08.2021237';
        $accountPersonalInformation->termsVersion = 1;

        $accountPersonalInformation->userAddress->streetAddress1 = '123 Main St.';
        $accountPersonalInformation->userAddress->city = 'Downtown';
        $accountPersonalInformation->userAddress->state = 'NJ';
        $accountPersonalInformation->userAddress->postalCode = '12345';
        $accountPersonalInformation->userAddress->country = 'USA';

        $accountPersonalInformation->mailingAddress->streetAddress1 = '123 Main St.';
        $accountPersonalInformation->mailingAddress->city = 'Downtown';
        $accountPersonalInformation->mailingAddress->state = 'NJ';
        $accountPersonalInformation->mailingAddress->postalCode = '12345';
        $accountPersonalInformation->mailingAddress->country = 'USA';
        
        return $accountPersonalInformation;
    }
    public static function getThreatRiskData()
    {
        $threatRiskData = new ThreatRiskData();
        $threatRiskData->merchantSourceIp = '8.8.8.8';
        $threatRiskData->threatMetrixPolicy = 'Default';
        $threatRiskData->threatMetrixSessionId = 'dad889c1-1ca4-4fq71-8f6f-807eb4408bc7';
        
        return $threatRiskData;
    }
    
    public static function getSignificantOwnerData()
    {
        $significantOwnerData = new SignificantOwnerData();
        $significantOwnerData->authorizedSignerFirstName = 'John';
        $significantOwnerData->authorizedSignerLastName = 'Doe';
        $significantOwnerData->authorizedSignerTitle = 'Director';
        
        $significantOwnerData->significantOwnerData->firstName = 'John';
        
        return $significantOwnerData;
    }
    
    public static function getBeneficialOwnerData()
    {
        $ownersInformation = new BeneficialOwnerData();
        $firstOwnerInformation = new OwnersData();
        $firstOwnerInformation->firstName = 'First1';
        $firstOwnerInformation->lastName = 'Last1';
        $firstOwnerInformation->title = 'CEO';
        $firstOwnerInformation->email = 'abc@qamail.com';
        $firstOwnerInformation->dateOfBirth = '11-11-1988';
        $firstOwnerInformation->ssn = 123545677;
        $firstOwnerInformation->ownerAddress->streetAddress1 = '125 Main St.';
        $firstOwnerInformation->ownerAddress->city = 'Downtown';
        $firstOwnerInformation->ownerAddress->state = 'NJ';
        $firstOwnerInformation->ownerAddress->postalCode = '12345';
        $firstOwnerInformation->ownerAddress->country = 'USA';

        $secondOwnerInformation = new OwnersData();
        $secondOwnerInformation->firstName = 'First4';
        $secondOwnerInformation->lastName = 'Last4';
        $secondOwnerInformation->title = 'Director';
        $secondOwnerInformation->email = 'abc1@qamail.com';
        $secondOwnerInformation->dateOfBirth = '11-11-1989';
        $secondOwnerInformation->ssn = 123545677;
        $secondOwnerInformation->ownerAddress->streetAddress1 = '125 Main St.';
        $secondOwnerInformation->ownerAddress->city = 'Downtown';
        $secondOwnerInformation->ownerAddress->state = 'NJ';
        $secondOwnerInformation->ownerAddress->postalCode = '12345';
        $secondOwnerInformation->ownerAddress->country = 'USA';

        $ownersInformation->ownersCount = 2;
        $ownersInformation->ownersList = [$firstOwnerInformation, $secondOwnerInformation];
        
        return $ownersInformation;
    }

    public static function getBeneficialOwnerDataCA()
    {
        $ownersInformation = new BeneficialOwnerData();
        $firstOwnerInformation = new OwnersData();
        $firstOwnerInformation->firstName = 'Style';
        $firstOwnerInformation->lastName = 'Stallone';
        $firstOwnerInformation->title = 'CEO';
        $firstOwnerInformation->email = 'abc@qamail.com';
        $firstOwnerInformation->dateOfBirth = '11-11-1988';
        $firstOwnerInformation->ssn = 123545677;
        $firstOwnerInformation->ownerAddress->streetAddress1 = '3400 N Ashton Blvd';
        $firstOwnerInformation->ownerAddress->city = 'Orlando';
        $firstOwnerInformation->ownerAddress->state = 'FL';
        $firstOwnerInformation->ownerAddress->postalCode = 'X0A 0H0';
        $firstOwnerInformation->ownerAddress->country = 'CAN';

        $secondOwnerInformation = new OwnersData();
        $secondOwnerInformation->firstName = 'Thomas';
        $secondOwnerInformation->lastName = 'Hanks';
        $secondOwnerInformation->title = 'Director';
        $secondOwnerInformation->email = 'abc1@qamail.com';
        $secondOwnerInformation->dateOfBirth = '11-11-1989';
        $secondOwnerInformation->ssn = 123545677;
        $secondOwnerInformation->ownerAddress->streetAddress1 = '1970 Diamond Blvd';
        $secondOwnerInformation->ownerAddress->city = 'Orlando';
        $secondOwnerInformation->ownerAddress->state = 'FL';
        $secondOwnerInformation->ownerAddress->postalCode = 'X0A 0H0';
        $secondOwnerInformation->ownerAddress->country = 'CAN';

        $ownersInformation->ownersCount = 2;
        $ownersInformation->ownersList = [$firstOwnerInformation, $secondOwnerInformation];
        
        return $ownersInformation;
    }
    
    public static function getCreditCardData()
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
        
        return $card;
    }
    
    public static function getACHData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountNumber = '123456789';
        $bankAccountInformation->accountType = 'C';
        $bankAccountInformation->routingNumber = '102000076';
        
        return $bankAccountInformation;
    }

    public static function getMailingAddress()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->postalCode = "12345";
        $address->country = "USA";
        return $address;
    }

    public static function getSecondaryBankAccountData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountCountryCode = 'USA';
        $bankAccountInformation->accountName = 'MyBankAccount';
        $bankAccountInformation->accountNumber = '123456788';
        $bankAccountInformation->accountOwnershipType = 'Personal';
        $bankAccountInformation->accountType = 'C';
        $bankAccountInformation->routingNumber = '102000076';
        $bankAccountInformation->bankName = 'My Bank';
        
        return $bankAccountInformation;
    }
    
    public static function getGrossBillingSettleData()
    {
        $grossBillingInformation = new GrossBillingInformation();
        
        $grossBillingInformation->grossSettleBankData->accountCountryCode = 'USA';
        $grossBillingInformation->grossSettleBankData->accountName = 'Scott Sterling';
        $grossBillingInformation->grossSettleBankData->accountNumber = '111222333';
        $grossBillingInformation->grossSettleBankData->accountOwnershipType = 'Personal';
        $grossBillingInformation->grossSettleBankData->accountType = 'C';
        $grossBillingInformation->grossSettleBankData->routingNumber = '124002971';
        $grossBillingInformation->grossSettleBankData->accountHolderName = 'Scott Sterling';
        
        $grossBillingInformation->grossSettleAddress->streetAddress1 = '123 Main St.';
        $grossBillingInformation->grossSettleAddress->city = 'Downtown';
        $grossBillingInformation->grossSettleAddress->state = 'NJ';
        $grossBillingInformation->grossSettleAddress->postalCode = '12345';
        $grossBillingInformation->grossSettleAddress->country = 'USA';
        
        $grossBillingInformation->grossSettleCreditCardData->number = '4111111111111111';
        $grossBillingInformation->grossSettleCreditCardData->expMonth = 12;
        $grossBillingInformation->grossSettleCreditCardData->expYear = 2025;
        $grossBillingInformation->grossSettleCreditCardData->cvn = '123';
        $grossBillingInformation->grossSettleCreditCardData->cardHolderName = 'Joe Smith';
        
        
        return $grossBillingInformation;
    }
    
    public static function editAccountPermissions()
    {
        $accountPermissions = new AccountPermissions();
        $accountPermissions->ccProcessing = 'Y';

        return $accountPermissions;
    }
    
    public static function editUserPersonalData()
    {
        $accountPersonalInformation = new UserPersonalData();
        $accountPersonalInformation->dayPhone = 4464464464;
        $accountPersonalInformation->eveningPhone = 4464464464;
        $accountPersonalInformation->externalId = uniqid();
        $accountPersonalInformation->firstName = 'John';
        $accountPersonalInformation->lastName = 'Doe';
        $accountPersonalInformation->phonePin = 1234;
        $accountPersonalInformation->sourceEmail = sprintf("user%s@user.com", mt_rand(1, 10000));
        $accountPersonalInformation->notificationEmail = sprintf("user%s@user.com", mt_rand(1, 10000));
        $accountPersonalInformation->ssn = 123456789;
        $accountPersonalInformation->dateOfBirth = '01-01-1981';
        $accountPersonalInformation->tier = 'TestEIN';
        
        return $accountPersonalInformation;
    }
    
    public static function editAddressData()
    {
        $accountPersonalInformation = new UserPersonalData();
        $accountPersonalInformation->userAddress->streetAddress1 = '124 Main St.';
        $accountPersonalInformation->userAddress->city = 'Downtown';
        $accountPersonalInformation->userAddress->state = 'NJ';
        $accountPersonalInformation->userAddress->postalCode = '12345';
        $accountPersonalInformation->userAddress->country = 'USA';
        
        $accountPersonalInformation->mailingAddress->streetAddress1 = '125 Main St.';
        $accountPersonalInformation->mailingAddress->city = 'Downtown';
        $accountPersonalInformation->mailingAddress->state = 'NJ';
        $accountPersonalInformation->mailingAddress->postalCode = '12345';
        $accountPersonalInformation->mailingAddress->country = 'USA';
        
        return $accountPersonalInformation;
    }
    
    public static function editBankData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountCountryCode = 'USA';
        $bankAccountInformation->accountName = 'Sterling';
        $bankAccountInformation->accountNumber = '111111111';
        $bankAccountInformation->accountOwnershipType = 'Business';
        //Valid values are: Checking, Savings, and GeneralLedger
        $bankAccountInformation->accountType = 'Checking';
        $bankAccountInformation->routingNumber = '91000019';
        $bankAccountInformation->bankName = 'Bank Name';
        
        return $bankAccountInformation;
    }
    
    public static function editACHData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountNumber = '123456789';
        //Valid values are: Checking, Savings, and GeneralLedger
        $bankAccountInformation->accountType = 'Savings';
        $bankAccountInformation->routingNumber = '102000076';
        
        return $bankAccountInformation;
    }

    public static function getDeviceData($numOfDeviceTypes = 1, $withAttr = true)
    {
        $deviceTypes = ["PAX S500", "Secure Submit"];
        $deviceList = new DeviceDetails();

        for ($i = 0; $i < $numOfDeviceTypes; $i++) {
            $deviceData = new DeviceDetails();
            if ($i > count($deviceTypes)) {
                break;
            }

            $deviceData->name = $deviceTypes[$i];
            $deviceData->quantity = 1;

            if ($withAttr) {
                $deviceData->attributes->name = "Heartland.AMD.OfficeKey";
                $deviceData->attributes->value = "123456";
            }
            $deviceList->append($deviceData);
        }

        return $deviceList;
    }

    public static function getDevicePhysicalData($numOfDeviceTypes = 1, $withAttr = true)
    {
        $deviceTypes = ["PAX S300" ,"TestDevice"];
        $deviceList = new DeviceDetails();

        for ($i = 0; $i < $numOfDeviceTypes; $i++) {
            $deviceData = new DeviceDetails();
            if ($i > count($deviceTypes)) {
                break;
            }

            $deviceData->name = $deviceTypes[$i];
            $deviceData->quantity = 1;

            if ($withAttr) {
                $deviceData->attributes->name = "Canada.CP.Language";
                $deviceData->attributes->value = "en";
            }
            $deviceList->append($deviceData);
        }

        return $deviceList;
    }

    public static function getDeviceDataForOrderDevice($numOfDeviceTypes = 1, $withAttr = true)
    {
        $deviceTypes = ["Secure Submit"];
        $deviceList = new DeviceDetails();

        for ($i = 0; $i < $numOfDeviceTypes; $i++) {
            $deviceData = new DeviceDetails();
            if ($i > count($deviceTypes)) {
                break;
            }

            $deviceData->name = $deviceTypes[$i];
            $deviceData->quantity = 1;

            if ($withAttr) {
                $deviceData->attributes->name = "Heartland.AMD.OfficeKey";
                $deviceData->attributes->value = "123456";
            }
            $deviceList->append($deviceData);
        }

        return $deviceList;
    }

    public static function getOrderNewDeviceData()
    {
        $orderDevice = new DeviceOrder();
        $orderDevice->accountNumber = "718581359";
        $orderDevice->shipTo = "Test Company";
        $orderDevice->shipToContact = "John Q. Public";
        $orderDevice->shipToAddress = "2675 W 600 N";
        $orderDevice->shipToAddress2 = "Apt G";
        $orderDevice->shipToCity = "Lindon";
        $orderDevice->shipToState = "UT";
        $orderDevice->shipToZip = "84042";
        $orderDevice->shipToPhone = "801-555-1212";
        $orderDevice->cardholderName = "Johnny Cage";
        $orderDevice->ccNum = "4111111111111111";
        $orderDevice->expDate = "0427";
        $orderDevice->cvv2 = "999";
        $orderDevice->billingZip = "84003";

        return $orderDevice;
    }

    public static function getRenewAccountDetails()
    {
        $renewAccountData = new RenewAccountData();
        $renewAccountData->tier = 'TestEIN';
        $renewAccountData->zipCode = '12345';
        $renewAccountData->creditCard->number = '4111111111111111';
        $renewAccountData->creditCard->expMonth = 12;
        $renewAccountData->creditCard->expYear = 2025;
        $renewAccountData->creditCard->cvn = 123;
        $renewAccountData->paymentBankAccountNumber = '123456789';
        $renewAccountData->paymentBankRoutingNumber = '102000076';
        $renewAccountData->paymentBankAccountType = 'Checking';
        
        return $renewAccountData;
    }
}
