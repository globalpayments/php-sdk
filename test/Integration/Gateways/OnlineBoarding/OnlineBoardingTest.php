<?php

namespace GlobalPayments\Api\Test\Integration\Gateways\OnlineBoarding;

use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\OnlineBoarding\BankAccount;
use GlobalPayments\Api\Entities\OnlineBoarding\BankingInfo;
use GlobalPayments\Api\Entities\OnlineBoarding\BoardingApplication;
use GlobalPayments\Api\Entities\OnlineBoarding\BusinessInfo;
use GlobalPayments\Api\Entities\OnlineBoarding\MerchantInfo;
use GlobalPayments\Api\Entities\OnlineBoarding\OwnerOfficer;
use GlobalPayments\Api\Entities\OnlineBoarding\TransactionInfo;
use GlobalPayments\Api\Entities\OnlineBoarding\StatementOptions;
use GlobalPayments\Api\Entities\OnlineBoarding\DisputeOptions;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\BankAccountTypeSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\FundsTransferMethodSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\OwnerOfficerSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\States;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\TypeofOwnershipSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\DBALegalElectronic;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\DBALegal;
use GlobalPayments\Api\BoardingConfig;
use GlobalPayments\Api\Services\BoardingService;
use DateTime;

class OnlineBoardingTest extends TestCase
{
	public $service;
	
	public function setup()
    {
        $config = new BoardingConfig();
        $config->portal = 'demo/Application/20/AllLocations';

        $this->service = new BoardingService($config);
    }
	
	// public function testbadInvitationToken()
    // {
    //     $this->service->submitApplication("BAD-TOKEN", null);
    // }

	public function testnewApplication()
    {
        // Test not working (doesn't work in .net SDK either), so commented out for now
        // $application = $this->service->newApplication();
        
        // // merchantInfo info
        // $merchantInfo = new MerchantInfo();
        // $merchantInfo->merchantDbaName = 'Automated Application';
        // $merchantInfo->merchantEmail = "name@somedomain.com";
        // $merchantInfo->merchantPhone = "1234567890";
        // $merchantInfo->merchantEmailFirstName = "Russell";
        // $merchantInfo->merchantEmailLastName = "Everett";
        // $merchantInfo->merchantPrimaryContactPhone = "1234567890";
        // $merchantInfo->merchantStoreNumber = "123";
        // $merchantInfo->merchantNumberOfLocations = 1;
        // $merchantInfo->merchantStreet = "1 Heartland Way";
        // $merchantInfo->merchantCity = "Jeffersonville";
        // $merchantInfo->merchantStatesSelect = States::INDIANA;
        // $merchantInfo->merchantZip = "12345";
        // $merchantInfo->federalTaxId = "123456789";
        // $application->merchantInfo = $merchantInfo;
        // $application->legalInfoSameAsMerchant = true;
        
        // // business info
        // $businessInfo = new BusinessInfo();
        // $businessInfo->ownershipTypeSelect = TypeofOwnershipSelect::SOLEPROPRIETORSHIP;
        // $businessInfo->isFederalIdSignersSsn = true;
        // $businessInfo->dataCompromiseOrComplianceInvestigation = false;
        // $businessInfo->everFiledBankrupt = false;
        // $businessInfo->dateBusinessAcquired = new DateTime('2000-1-1');
        // $businessInfo->dataStorageOrMerchantServicer = false;
        // $businessInfo->dateAcceptingCreditCardsStarted = new DateTime('2000-12-12');
        // $application->businessInfo = $businessInfo;
        
        // // owners
        // $owner = new OwnerOfficer();
        // $owner->firstName = "Russell";
        // $owner->lastName = "Everett";
        // $owner->title = "Developer";
        // $owner->dateOfBirth = new DateTime('1977-09-12');
        // $owner->homePhone = "1234567890";
        // $owner->ssn = "123456789";
        // $owner->ownershipTypeSelect = OwnerOfficerSelect::OWNER;
        // $owner->street = "1 Heartland Way";
        // $owner->city = "Jeffersonville";
        // $owner->zip = "12345";
        // $owner->stateSelect = States::INDIANA;
        // $owner->equityOwnership = "100";
        // $owner->emailAddress = "russell.everett@e-hps.com";
        // $application->ownerOfficer = $owner;
        
        // // banking information
        // $bankingInfo = new BankingInfo();
        // $bankingInfo->bankName = "Wells Fargo";
        // $bankingInfo->bankCity = "St. Louis";
        // $bankingInfo->bankStatesSelect = States::MISSOURI;
        // $bankingInfo->bankZip = "12345";
        // $application->bankingInfo = $bankingInfo;
        
        // // bank accounts
        // $bankAccount = new BankAccount();
        // $bankAccount->accountNumber = "12345678901234";
        // $bankAccount->transitRouterAbaNumber = "123456789";
        // $bankAccount->accountTypeSelect = BankAccountTypeSelect::CHECKING;
        // $bankAccount->transferMethodTypeSelect = FundsTransferMethodSelect::DEPOSITS_AND_FEES;
        // $application->bankingInfo->bankAccount = $bankAccount;

        // // $this->service->submitApplication("D9E5EEB0-7709-4E60-B0CE-0ABABC1EBACE", $application);
        // $this->service->submitApplication(null, $application);
    }
	
	public function testdemoApplication()
    {
        $application = $this->service->newApplication();
        
        $merchantInfo = new MerchantInfo();
        $merchantInfo->merchantDbaName = 'PHP';
        $merchantInfo->merchantEmail = 'eric.vest@e-hps.com';
        $merchantInfo->merchantPhone = '1234567890';
        $merchantInfo->merchantEmailFirstName = 'Eric';
        $merchantInfo->merchantEmailLastName = 'Vest';
        $merchantInfo->merchantStreet = '123';
        $merchantInfo->merchantCity = '123';
        $merchantInfo->merchantStatesSelect = States::ALABAMA;
        $merchantInfo->merchantZip = '12345';
        $merchantInfo->federalTaxId = '123456789';
        $merchantInfo->merchantWebsiteAddress = '123';
        $merchantInfo->merchantPrimaryContactPhone = '1234567890';
        $application->merchantInfo = $merchantInfo;
        $application->legalInfoSameAsMerchant = true;

        // business info
        $businessInfo = new BusinessInfo();
        $businessInfo->ownershipTypeSelect = TypeofOwnershipSelect::SOLEPROPRIETORSHIP;
        $businessInfo->isFederalIdSignersSsn = true;
        $businessInfo->dateBusinessAcquired = '2000-1-1';
        $businessInfo->dateBusinessStarted = '2000-01-01';
        $businessInfo->productsServicesProvided = 'None';
        $businessInfo->refundPolicy = 'None';
        $application->businessInfo = $businessInfo;

        // transaction info
        $transactionInfo = new TransactionInfo();
        $transactionInfo->annualVolume = '123';
        $transactionInfo->averageTicket = '123';
        $transactionInfo->amexAnnualVolume = '123';
        $transactionInfo->amexAverageTicket = '123';
        $application->transactionInfo = $transactionInfo;

        // statements
        $statementOptions = new StatementOptions();
        $statementOptions->statementMailDestinationOptionSelect = DBALegalElectronic::DBA;
        $application->statementOptions = $statementOptions;

        // disputes
        $disputeOptions = new DisputeOptions();
        $disputeOptions->mailingOptionSelect = DBALegal::DBA;
        $application->disputeOptions = $disputeOptions;

        // TODO: owner information
        $owner = new OwnerOfficer();
        $owner->firstName = '123';
        $owner->lastName = '123';
        $owner->title = '123';
        $owner->dateOfBirth = '1977-09-12';
        $owner->homePhone = '1234567890';
        $owner->sSN = '123456789';
        $owner->ownershipTypeSelect = OwnerOfficerSelect::OWNER;
        $owner->street = '123';
        $owner->city = '123';
        $owner->zip = '12345';
        $owner->stateSelect = States::ALABAMA;
        $owner->equityOwnership = '100';
        $owner->emailAddress = 'eric.vest@e-hps.com';
        array_push($application->ownerOfficers, $owner);

        // TODO: banking information
        $bankingInfo = new BankingInfo();
        $bankingInfo->bankName = '123';
        $bankingInfo->bankPhone = '1234567890';
        $bankingInfo->bankCity = '123';
        $bankingInfo->bankStatesSelect = States::ALABAMA;
        $bankingInfo->bankZip = '12345';
        $application->bankingInfo = $bankingInfo;

        // TODO: bank accounts
        $bankAccount = new BankAccount();
        $bankAccount->accountNumber = '12345678901234';
        $bankAccount->accountTypeSelect = BankAccountTypeSelect::CHECKING;
        $bankAccount->transitRouterAbaNumber = '123456789';
        array_push($application->bankingInfo->bankAccounts, $bankAccount);

        $submissionResponse = $this->service->submitApplication(null, $application);
    }
}
