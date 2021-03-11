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
    
    public function setup()
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

    public function testCreateAccount()
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
        
        $response = PayFacService::createAccount()
                    ->withBankAccountData($bankAccountInformation)
                    ->withBusinessData($userBusinessInformation)
                    ->withUserPersonalData($accountPersonalInformation)
                    ->withThreatRiskData($threatRiskData)
                    ->withSignificantOwnerData($significantOwnerData)
                    ->withBeneficialOwnerData($ownersInformation)
                    ->withCreditCardData($creditCardInformation)
                    ->withACHData($achInformation)
                    ->withSecondaryBankAccountData($secondaryBankInformation)
                    ->execute();
                    
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->accountNumber);
        $this->assertNotNull($response->payFacData->password);
        $this->assertNotNull($response->payFacData->sourceEmail);
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
                    ->withPassword('testPwd_'.rand(1, 100))
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
                    ->withAccountNumber(718136530)
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
        $response = PayFacService::editAccount()
        ->withAccountNumber(718136530)
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
        //Owners count shoud not be excedded 6
        $response = PayFacService::updateBeneficialOwnershipInfo()
        ->withAccountNumber(718151188)
        ->withBeneficialOwnerData(TestAccountData::getBeneficialOwnerData())
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->payFacData->beneficialOwnerDataResult);
    }
    
    public function testDisownAccount()
    {
        //Enter active account number
        $response = PayFacService::disownAccount()
        ->withAccountNumber(718150922)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
    
    public function testUploadChargebackDocument()
    {
        $documentDetails = new UploadDocumentData();
        $documentDetails->transactionReference = '123456789';
        $documentDetails->documentName = 'ChargebackDispute';
        $documentDetails->documentLocation = __DIR__ . '/TestData/ChargebackDispute.jpg';
        
        $response = PayFacService::uploadDocumentChargeback()
        ->withAccountNumber(718134349)
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
        $response = PayFacService::updateBankAccountOwnershipInfo()
        ->withAccountNumber(718136530)
        ->withBeneficialOwnerData(TestAccountData::getBeneficialOwnerData())
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
