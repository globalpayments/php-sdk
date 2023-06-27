<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\ProPay;

use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Entities\PayFac\UploadDocumentData;
use GlobalPayments\Api\Entities\PayFac\SingleSignOnData;
use GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData\TestAccountData;

class ProPayAccountTests extends TestCase
{

    public function setup(): void
    {
        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->certificationStr = '5dbacb0fc504dd7bdc2eadeb7039dd'; 
        $config->terminalId = '7039dd';
        $config->environment = Environment::TEST;
        $config->selfSignedCertLocation = __DIR__ . '/TestData/selfSignedCertificate.crt';
        return $config;
    }

    
    public function test01CreateAccount()
    {
        $bankAccountInformation = TestAccountData::getBankAccountData();
        $userBusinessInformation = TestAccountData::getBusinessData();
        $accountPersonalInformation = TestAccountData::getUserPersonalData();
        $threatRiskData = TestAccountData::getThreatRiskData();
        $significantOwnerData = TestAccountData::getSignificantOwnerData();
        $ownersInformation = TestAccountData::getBeneficialOwnerData();
        $creditCardInformation = TestAccountData::getCreditCardData();
        $achInformation = TestAccountData::getACHData();
        $secondaryBankInformation = TestAccountData::getSecondaryBankAccountData();
        $mailingAddressInfo = TestAccountData::getMailingAddress();
        $deviceData = TestAccountData::getDeviceData(1, false);

        $response = PayFacService::createAccount()
            ->withBankAccountData($bankAccountInformation)
            ->withBusinessData($userBusinessInformation)
            ->withUserPersonalData($accountPersonalInformation)
            ->withThreatRiskData($threatRiskData)
            ->withSignificantOwnerData($significantOwnerData)
            ->withBeneficialOwnerData($ownersInformation)
            ->withCreditCardData($creditCardInformation)
            ->withACHData($achInformation)
            ->withDeviceData($deviceData)
            ->withMailingAddress($mailingAddressInfo)
            ->withTimeZone("ET") // with TimeZone
            ->withSecondaryBankAccountData($secondaryBankInformation)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->accountNumber);
        $this->assertNotNull($response->payFacData->password);
        $this->assertNotNull($response->payFacData->sourceEmail);
    }

    public function test02CreateAccountForDeviceOrder()
    {
        sleep(5);
        $bankAccountInformation = TestAccountData::getBankAccountData();
        $userBusinessInformation = TestAccountData::getBusinessData();
        $accountPersonalInformation = TestAccountData::getUserPersonalData();
        $ownersInformation = TestAccountData::getBeneficialOwnerData();
        $mailingAddressInfo = TestAccountData::getMailingAddress();
        $creditCardInformation = TestAccountData::getCreditCardData();
        $deviceData = TestAccountData::getDeviceData(1, false);

        $response = PayFacService::createAccount()
            ->withBankAccountData($bankAccountInformation)
            ->withBusinessData($userBusinessInformation)
            ->withUserPersonalData($accountPersonalInformation)
            ->withBeneficialOwnerData($ownersInformation)
            ->withCreditCardData($creditCardInformation)
            ->withMailingAddress($mailingAddressInfo)
            ->withDeviceData($deviceData)
            ->withTimeZone("ET")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->accountNumber);
        $this->assertNotNull($response->payFacData->password);
        $this->assertNotNull($response->payFacData->sourceEmail);
    }

    public function test03CreateAccountPhysicalDevice()
    {
        $bankAccountInformation = TestAccountData::getBankAccountData();
        $userBusinessInformation = TestAccountData::getBusinessData();
        $accountPersonalInformation = TestAccountData::getUserPersonalData();
        $ownersInformation = TestAccountData::getBeneficialOwnerData();
        $creditCardInformation = TestAccountData::getCreditCardData();
        $mailingAddressInfo = TestAccountData::getMailingAddress();
        $deviceData = TestAccountData::getDevicePhysicalData(1, true);

        $response = PayFacService::createAccount()
            ->withBankAccountData($bankAccountInformation)
            ->withBusinessData($userBusinessInformation)
            ->withUserPersonalData($accountPersonalInformation)
            ->withBeneficialOwnerData($ownersInformation)
            ->withCreditCardData($creditCardInformation)
            ->withDeviceData($deviceData)
            ->withMailingAddress($mailingAddressInfo)
            ->withTimeZone("ET")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->accountNumber);
        $this->assertNotNull($response->payFacData->password);
        $this->assertNotNull($response->payFacData->sourceEmail);
    }

    public function test04CreateAccount()
    {
        $bankAccountInformation = TestAccountData::getBankAccountData();
        $userBusinessInformation = TestAccountData::getBusinessData();
        $accountPersonalInformation = TestAccountData::getUserPersonalData();
        $threatRiskData = TestAccountData::getThreatRiskData();
        $significantOwnerData = TestAccountData::getSignificantOwnerData();
        $ownersInformation = TestAccountData::getBeneficialOwnerData();
        $creditCardInformation = TestAccountData::getCreditCardData();
        $achInformation = TestAccountData::getACHData();
        $secondaryBankInformation = TestAccountData::getSecondaryBankAccountData();
        $mailingAddressInfo = TestAccountData::getMailingAddress();
        $deviceData = TestAccountData::getDeviceData(1, false);
        $accountPersonalInformation->dateOfBirth = "01-01-1971";

        $response = PayFacService::createAccount()
            ->withBankAccountData($bankAccountInformation)
            ->withBusinessData($userBusinessInformation)
            ->withUserPersonalData($accountPersonalInformation)
            ->withThreatRiskData($threatRiskData)
            ->withSignificantOwnerData($significantOwnerData)
            ->withBeneficialOwnerData($ownersInformation)
            ->withCreditCardData($creditCardInformation)
            ->withACHData($achInformation)
            ->withDeviceData($deviceData)
            ->withMailingAddress($mailingAddressInfo)
            ->withTimeZone("ET")
            ->withSecondaryBankAccountData($secondaryBankInformation)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('66', $response->responseCode); // API Boarding Test Failed KYC (Status 66)
    }

    public function test05OrderNewDevice()
    {
        $orderDeviceInfo = TestAccountData::getOrderNewDeviceData();
        $deviceData = TestAccountData::getDeviceDataForOrderDevice(1, false);

        $response = PayFacService::orderDevice()
            ->withDeviceDetails($orderDeviceInfo)
            ->withDeviceData($deviceData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditAccountInformation()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718138433)
            ->withUserPersonalData(TestAccountData::editUserPersonalData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditPassword()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718138433)
            ->withPassword('testPwd_' . rand(1, 100))
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditAddress()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718138433)
            ->withUserPersonalData(TestAccountData::editAddressData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditAccountPermissions()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718583526)
            ->withAccountPermissions(TestAccountData::editAccountPermissions())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditPrimaryBankAccount()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718150930)
            ->withBankAccountData(TestAccountData::editBankData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditSecondaryBankAccount()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718150930)
            ->withSecondaryBankAccountData(TestAccountData::editBankData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditBusinessData()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718150930)
            ->withBusinessData(TestAccountData::getBusinessData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditCreditCard()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718138433)
            ->withCreditCardData(TestAccountData::getCreditCardData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditRenewalInformation()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718138433)
            ->withACHData(TestAccountData::editACHData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testEditNegativeLimit()
    {
        $this->markTestSkipped('Required additional configurations from Propay');
        // Required additional configurations from Propay
        $response = PayFacService::editAccount()
            ->withAccountNumber(718583533)
            ->withNegativeLimit(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testResetPassword()
    {
        $response = PayFacService::resetPassword()
            ->withAccountNumber(718150930)
            ->withNegativeLimit(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->password);
    }

    public function testRenewAccount()
    {
        $response = PayFacService::renewAccount()
            ->withAccountNumber(718151055)
            ->withRenewalAccountData(TestAccountData::getRenewAccountDetails())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testUpdateBeneficialOwnerData()
    {
        $this->markTestSkipped('Required new account number without owners details | and executed once');

        // Owners count shoud not be excedded 6
        // This account must have been created with a beneficial owner count specified, but no owner details passed
        $response = PayFacService::updateBeneficialOwnershipInfo()
            ->withAccountNumber(718583641)
            ->withBeneficialOwnerData(TestAccountData::getBeneficialOwnerData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->beneficialOwnerDataResult);
    }

    public function testDisownAccount()
    {
        //Enter active account number
        $this->markTestSkipped('To run this test you need to enter active account number');
        $response = PayFacService::disownAccount()
            ->withAccountNumber(718583546)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testUploadChargebackDocument()
    {
        $this->markTestSkipped('To run this test you need to valid transactionReference number | can be verify on production only');
        // Enter valid transactionReference number
        $documentDetails = new UploadDocumentData();
        $documentDetails->transactionReference = '345';
        $documentDetails->documentName = 'ChargebackDispute';
        $documentDetails->documentLocation = __DIR__ . '/TestData/ChargebackDispute.jpg';

        $response = PayFacService::uploadDocumentChargeback()
            ->withAccountNumber(718134204)
            ->withUploadDocumentData($documentDetails)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testUnderwriteDocument()
    {
        $documentDetails = new UploadDocumentData();
        $documentDetails->documentCategory = 'Verification';
        $documentDetails->documentName = 'ChargebackDispute';
        $documentDetails->documentLocation = __DIR__ . '/TestData/ChargebackDispute.jpg';

        $response = PayFacService::uploadDocument()
            ->withAccountNumber(718150930)
            ->withUploadDocumentData($documentDetails)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testObtainSSOKey()
    {
        $singleSignOnData = new SingleSignOnData();
        $singleSignOnData->referrerUrl = 'https://www.globalpaymentsinc.com/';
        $singleSignOnData->ipAddress = '40.81.11.219';
        $singleSignOnData->ipSubnetMask = '255.255.255.0';

        $response = PayFacService::obtainSSOKey()
            ->withAccountNumber(718150930)
            ->withSingleSignOnData($singleSignOnData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->authToken);
    }

    public function testUpdateBankAccountOwnershipInfo()
    {
        // This api request is not in scope for the ProPay/Portico US solution.
        $this->markTestSkipped('This api request is not in scope for the ProPay/Portico US solution. ');

        $response = PayFacService::updateBankAccountOwnershipInfo()
            ->withAccountNumber(718134204)
            ->withBeneficialOwnerData(TestAccountData::getBeneficialOwnerDataCA())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testUpdateGrossBillingInfo()
    {
        $response = PayFacService::editAccount()
            ->withAccountNumber(718151524)
            ->withGrossBillingSettleData(TestAccountData::getGrossBillingSettleData())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
}
