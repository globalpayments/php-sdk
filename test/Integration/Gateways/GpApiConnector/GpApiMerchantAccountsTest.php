<?php

namespace Gateways\GpApiConnector;

use DateTime;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\FundsStatus;
use GlobalPayments\Api\Entities\Enums\MerchantAccountsSortProperty;
use GlobalPayments\Api\Entities\Enums\MerchantAccountStatus;
use GlobalPayments\Api\Entities\Enums\MerchantAccountType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Enums\UsableBalanceMode;
use GlobalPayments\Api\Entities\Enums\UserType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\MerchantAccountSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\User;
use GlobalPayments\Api\PaymentMethods\FundsAccount;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData\TestAccountData;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class GpApiMerchantAccountsTest extends TestCase
{
    private DateTime $startDate;
    private DateTime $endDate;
    private string $accountId;
    /** @var GpApiConfig */
    private GpApiConfig $config;

    public function setup(): void
    {
        $this->setUpConfig();
        ServicesContainer::configureService($this->config);
        $this->startDate = (new DateTime('2022-05-21'));
        $this->endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->accountId = count($response->result) > 0 ? (reset($response->result))->id : null;
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): void
    {
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::PARTNER_SOLUTION_APP_ID;
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::PARTNER_SOLUTION_APP_KEY;

        $this->config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testFindAccountsInfo()
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($response);
        /** @var MerchantAccountSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals(MerchantAccountStatus::ACTIVE, $rs->status);
        }
    }

    public function testFindAccountsInfo_SearchByStatusActive()
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotNull($response);
        /** @var MerchantAccountSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals(MerchantAccountStatus::ACTIVE, $rs->status);
        }
    }

    public function testFindAccountsInfo_SearchByStatusInactive()
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::INACTIVE)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEmpty($response->result);
        $this->assertCount(0, $response->result);
    }

    public function testFindAccountsInfo_SearchByName()
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::ACCOUNT_NAME, "Sandbox FMA")
            ->execute();

        $this->assertNotNull($response);
        /** @var MerchantAccountSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals(MerchantAccountStatus::ACTIVE, $rs->status);
        }
    }

    public function testFindAccountsInfo_SearchById()
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::RESOURCE_ID, "FMA_a07e67cdfdc641c4a5fe77a7f9f96cdd")
            ->execute();

        $this->assertNotNull($response);
        /** @var MerchantAccountSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals(MerchantAccountStatus::ACTIVE, $rs->status);
        }
    }

    public function testAccountDetails()
    {
        /** @var MerchantAccountSummary $response */
        $response = ReportingService::accountDetail($this->accountId)
            ->execute();

        $this->assertEquals($this->accountId, $response->id);
    }

    public function testAccountDetails_RandomId()
    {
        $accountId = GenerationUtils::getGuid();

        /** @var MerchantAccountSummary $response */
        $errorFound = false;
        try {
            ReportingService::accountDetail($accountId)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported", $e->getMessage());
            $this->assertEquals('40042', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAccountDetails_NullId()
    {
        $errorFound = false;
        try {
            ReportingService::accountDetail('null')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - Account details does not exist for null", $e->getMessage());
            $this->assertEquals('40041', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testEditAccountInformation()
    {
        $this->markTestSkipped('GP-API sandbox limitation. Edit account is not enabled in sandbox.');

        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Address 1";
        $billingAddress->streetAddress3 = "foyer";
        $billingAddress->city = 'Atlanta';
        $billingAddress->state = 'GA';
        $billingAddress->postalCode = '30346';
        $billingAddress->country = 'US';

        $creditCardInformation = TestAccountData::getCreditCardData();

        $merchants = ReportingService::findMerchants(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->andWith(SearchCriteria::START_DATE, $this->startDate)
            ->execute();

        $this->assertNotEmpty($merchants->result);
        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, reset($merchants->result)->id)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotEmpty($response->result);
        /** @var MerchantAccountSummary $accountSummary */
        $index = array_search(
            MerchantAccountType::FUND_MANAGEMENT, array_column($response->result, 'type')
        );
        if ($index === false) {
            $this->fail(sprintf(
                "Account type %s not found in order to perform the edit action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }
        $accountSummary = $response->result[$index];
        if ($accountSummary->type == MerchantAccountType::FUND_MANAGEMENT) {
            $response = PayFacService::editAccount()
                ->withAccountNumber($accountSummary->id)
                ->withUserReference($merchant->userReference)
                ->withAddress($billingAddress)
                ->withCreditCardData($creditCardInformation)
                ->execute();

            $this->assertNotNull($response);
        }
    }

    /** Address Lookup is available only for MMA accounts and returns a list of addresses */
    public function testAccountAddressLookup()
    {
        $address = new Address();
        $address->postalCode = 'CB6 1AS';
        $address->streetAddress1 = '2649';
        $address->streetAddress2 = 'Primrose Cottage';
        /** @var MerchantAccountSummary $response */
        $response = ReportingService::accountDetail($this->config->accessTokenInfo->merchantManagementAccountID)
            ->withPaging(1, 10)
            ->where(SearchCriteria::ADDRESS, $address)
            ->execute();

        $this->assertNotCount(0, $response->addresses);
        $this->assertEquals($address->postalCode, $response->addresses->offsetGet(0)->postalCode);
    }

    public function testAccountAddressLookup_WithoutPostalCode()
    {
        $address = new Address();
        $address->streetAddress1 = '2649';
        $address->streetAddress2 = 'Primrose';

        $errorFound = false;
        try {
            ReportingService::accountDetail($this->config->accessTokenInfo->merchantManagementAccountID)
                ->withPaging(1, 10)
                ->where(SearchCriteria::ADDRESS, $address)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields:postal_code.", $e->getMessage());
            $this->assertEquals('40251', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAccountAddressLookup_WithoutAddressLine1And2()
    {
        $address = new Address();
        $address->postalCode = 'CB6 1AS';

        $errorFound = false;
        try {
            ReportingService::accountDetail($this->config->accessTokenInfo->merchantManagementAccountID)
                ->withPaging(1, 10)
                ->where(SearchCriteria::ADDRESS, $address)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields:line_1 or line_2.", $e->getMessage());
            $this->assertEquals('40251', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testEditAccountInformation_WithoutCardDetails()
    {
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Address 1";
        $billingAddress->streetAddress3 = "foyer";
        $billingAddress->city = 'Atlanta';
        $billingAddress->state = 'GA';
        $billingAddress->postalCode = '30346';
        $billingAddress->country = 'US';

        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, reset($merchants->result)->id)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotEmpty($response->result);
        /** @var MerchantAccountSummary $accountSummary */
        $index = array_search(
            MerchantAccountType::FUND_MANAGEMENT, array_column($response->result, 'type')
        );
        if ($index === false) {
            $this->fail(sprintf(
                "Account type %s not found in order to perform the edit action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }
        $accountSummary = $response->result[$index];

        if ($accountSummary->type == MerchantAccountType::FUND_MANAGEMENT) {
            $errorFound = false;
            try {
                PayFacService::editAccount()
                    ->withAccountNumber($accountSummary->id)
                    ->withUserReference($merchant->userReference)
                    ->withAddress($billingAddress)
                    ->execute();
            } catch (GatewayException $e) {
                $errorFound = true;
                $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payer.payment_method.name", $e->getMessage());
                $this->assertEquals('40005', $e->responseCode);
            } finally {
                $this->assertTrue($errorFound);
            }
        }
    }

    public function testEditAccountInformation_WithoutAddress()
    {
        $creditCardInformation = TestAccountData::getCreditCardData();

        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, reset($merchants->result)->id)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotEmpty($response->result);
        /** @var MerchantAccountSummary $accountSummary */
        $index = array_search(
            MerchantAccountType::FUND_MANAGEMENT, array_column($response->result, 'type')
        );
        if ($index === false) {
            $this->fail(sprintf(
                "Account type %s not found in order to perform the edit action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }
        $accountSummary = $response->result[$index];

        if ($accountSummary->type == MerchantAccountType::FUND_MANAGEMENT) {
            $errorFound = false;
            try {
                PayFacService::editAccount()
                    ->withAccountNumber($accountSummary->id)
                    ->withUserReference($merchant->userReference)
                    ->withCreditCardData($creditCardInformation)
                    ->execute();
            } catch (GatewayException $e) {
                $errorFound = true;
                $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payer.billing_address.line_1", $e->getMessage());
                $this->assertEquals('40005', $e->responseCode);
            } finally {
                $this->assertTrue($errorFound);
            }
        }
    }

    public function testEditAccountInformation_WithoutId()
    {
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Address 1";
        $billingAddress->streetAddress3 = "foyer";
        $billingAddress->city = 'Atlanta';
        $billingAddress->state = 'GA';
        $billingAddress->postalCode = '30346';
        $billingAddress->country = 'US';

        $creditCardInformation = TestAccountData::getCreditCardData();

        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, reset($merchants->result)->id)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotEmpty($response->result);
        /** @var MerchantAccountSummary $accountSummary */
        $index = array_search(
            MerchantAccountType::FUND_MANAGEMENT, array_column($response->result, 'type')
        );
        if ($index === false) {
            $this->fail(sprintf(
                "Account type %s not found in order to perform the edit action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }
        $accountSummary = $response->result[$index];

        if ($accountSummary->type == MerchantAccountType::FUND_MANAGEMENT) {
            $errorFound = false;
            try {
                PayFacService::editAccount()
                    ->withUserReference($merchant->userReference)
                    ->withAddress($billingAddress)
                    ->withCreditCardData($creditCardInformation)
                    ->execute();
            } catch (BuilderException $e) {
                $errorFound = true;
                $this->assertEquals("accountNumber cannot be null for this transaction type.", $e->getMessage());
            } finally {
                $this->assertTrue($errorFound);
            }
        }
    }

    public function testEditAccountInformation_WithoutUserRef()
    {
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = "Address 1";
        $billingAddress->streetAddress3 = "foyer";
        $billingAddress->city = 'Atlanta';
        $billingAddress->state = 'GA';
        $billingAddress->postalCode = '30346';
        $billingAddress->country = 'US';

        $creditCardInformation = TestAccountData::getCreditCardData();

        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);

        $accountSummary = $this->getAccountByType(
            reset($merchants->result)->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountSummary)) {
            $this->fail(sprintf(
                "Account type %s not found in order to perform the edit action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        if ($accountSummary->type == MerchantAccountType::FUND_MANAGEMENT) {
            $errorFound = false;
            try {
                PayFacService::editAccount()
                    ->withAccountNumber($accountSummary->id)
                    ->withAddress($billingAddress)
                    ->withCreditCardData($creditCardInformation)
                    ->execute();
            } catch (GatewayException $e) {
                $errorFound = true;
                $this->assertEquals("Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported", $e->getMessage());
                $this->assertEquals('40042', $e->responseCode);
            } finally {
                $this->assertTrue($errorFound);
            }
        }
    }

    public function testTransferFundsAccount()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $transfer = $funds->transfer(1)
            ->withClientTransactionId('')
            ->withDescription('Transfer 1')
            ->execute();

        $this->assertNotNull($transfer);
        $this->assertEquals('SUCCESS', $transfer->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transfer->responseMessage);
    }

    public function testTransferFundsAccount_AllFields()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;
        $funds->usableBalanceMode = UsableBalanceMode::AVAILABLE_AND_PENDING_BALANCE;

        $transfer = $funds->transfer(1)
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('Transfer 1')
            ->execute();

        $this->assertNotNull($transfer);
        $this->assertEquals('SUCCESS', $transfer->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transfer->responseMessage);
    }

    public function testTransferFundsAccount_OnlyMandatoryFields()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $transfer = $funds->transfer(1)
            ->execute();

        $this->assertNotNull($transfer);
        $this->assertEquals('SUCCESS', $transfer->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transfer->responseMessage);
    }

    public function testTransferFundsAccount_WithIdempotency()
    {
        $idempotencyKey = GenerationUtils::getGuid();
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $transfer = $funds->transfer(1)
            ->withClientTransactionId(GenerationUtils::getGuid())
            ->withDescription('Transfer 1')
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertNotNull($transfer);
        $this->assertEquals('SUCCESS', $transfer->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transfer->responseMessage);

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId(GenerationUtils::getGuid())
                ->withDescription('Transfer 1')
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_WithoutRecipientAccountId()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->merchantId = $merchantSender->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId('')
                ->withDescription('Transfer 1')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Request expects the following conditionally mandatory fields recipient_account_id, recipient_account_name.', $e->getMessage());
            $this->assertEquals('40007', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_WithoutAccountIdAndAccountName()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId('')
                ->withDescription('Transfer 1')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Request expects the following conditionally mandatory fields account_id, account_name.', $e->getMessage());
            $this->assertEquals('40007', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_WithoutMerchantId()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = $accountRecipientSummary->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId('')
                ->withDescription('Transfer 1')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported', $e->getMessage());
            $this->assertEquals('40042', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_WithoutAmount()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(null)
                ->withClientTransactionId('')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields amount', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_RandomAccountId()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = GenerationUtils::getGuid();
        $funds->recipientAccountId = $accountRecipientSummary->id;
        $funds->merchantId = $merchantSender->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId('')
                ->withDescription('Transfer 1')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString('Status Code: INVALID_REQUEST_DATA - Merchant configuration does not exist for the following combination: ', $e->getMessage());
            $this->assertEquals('40041', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_RandomRecipientId()
    {
        $merchants = $this->getMerchants();

        $this->assertNotEmpty($merchants->result);
        $merchantSender = reset($merchants->result);
        $merchantRecipient = $merchants->result[1];
        /** @var MerchantAccountSummary $accountSenderSummary */
        $accountSenderSummary = $this->getAccountByType(
            $merchantSender->id,
            MerchantAccountType::FUND_MANAGEMENT
        );
        if (empty($accountSenderSummary)) {
            $this->fail(sprintf(
                "Account sender type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $accountRecipientSummary = $this->getAccountByType(
            $merchantRecipient->id,
            MerchantAccountType::FUND_MANAGEMENT
        );

        if (empty($accountRecipientSummary)) {
            $this->fail(sprintf(
                "Account recipient type %s not found in order to perform the transfer action",
                MerchantAccountType::FUND_MANAGEMENT
            ));
        }

        $funds = new FundsAccount();
        $funds->accountId = $accountSenderSummary->id;
        $funds->accountName = $accountSenderSummary->name;
        $funds->recipientAccountId = GenerationUtils::getGuid();
        $funds->merchantId = $merchantSender->id;

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->withClientTransactionId('')
                ->withDescription('Transfer 1')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Transfers may only be initiated between accounts under the same partner program', $e->getMessage());
            $this->assertEquals('40041', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransferFundsAccount_RandomMerchantId()
    {
        $funds = new FundsAccount();
        $funds->accountId = GenerationUtils::getGuid();
        $funds->recipientAccountId = GenerationUtils::getGuid();
        $funds->merchantId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $funds->transfer(1)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported', $e->getMessage());
            $this->assertEquals('40042', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAddFunds()
    {
        $amount = "10";
        $currency = "USD";
        $accountId = "FMA_a78b841dfbd14803b3a31e4e0c514c72";
        $merchantId = 'MER_5096d6b88b0b49019c870392bd98ddac';
        $merchant = User::fromId($merchantId, UserType::MERCHANT);

        /** @var User $response */
        $response = PayFacService::addFunds()
            ->withAmount($amount)
            ->withAccountNumber($accountId)
            ->withUserReference($merchant->userReference)
            ->withPaymentMethodName(PaymentMethodName::BANK_TRANSFER)
            ->withPaymentMethodType(PaymentMethodType::CREDIT)
            ->withCurrency($currency)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertNotNull($response->fundsAccountDetails);
        $this->assertEquals(FundsStatus::CAPTURED, $response->fundsAccountDetails->status);
        $this->assertEquals($amount, $response->fundsAccountDetails->amount);
        $this->assertEquals($currency, $response->fundsAccountDetails->currency);
        $this->assertEquals('CREDIT', $response->fundsAccountDetails->paymentMethodType);
        $this->assertEquals('BANK_TRANSFER', $response->fundsAccountDetails->paymentMethodName);
        $this->assertNotNull($response->fundsAccountDetails->account);
        $this->assertEquals($accountId, $response->fundsAccountDetails->account->id);
    }

    public function testAddFunds_OnlyMandatory()
    {
        $amount = "10";
        $accountId = "FMA_a78b841dfbd14803b3a31e4e0c514c72";
        $merchantId = 'MER_5096d6b88b0b49019c870392bd98ddac';
        $merchant = User::fromId($merchantId, UserType::MERCHANT);

        /** @var User $response */
        $response = PayFacService::addFunds()
            ->withAmount($amount)
            ->withAccountNumber($accountId)
            ->withUserReference($merchant->userReference)
            ->withPaymentMethodType(PaymentMethodType::CREDIT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertNotNull($response->fundsAccountDetails);
        $this->assertEquals(FundsStatus::CAPTURED, $response->fundsAccountDetails->status);
        $this->assertEquals($amount, $response->fundsAccountDetails->amount);
        $this->assertEquals('CREDIT', $response->fundsAccountDetails->paymentMethodType);
        $this->assertEquals('BANK_TRANSFER', $response->fundsAccountDetails->paymentMethodName);
        $this->assertNotNull($response->fundsAccountDetails->account);
        $this->assertEquals($accountId, $response->fundsAccountDetails->account->id);
    }

    public function testAddFunds_InsufficientFunds()
    {
        $amount = "10000";
        $accountId = "FMA_a78b841dfbd14803b3a31e4e0c514c72";
        $merchantId = 'MER_5096d6b88b0b49019c870392bd98ddac';
        $merchant = User::fromId($merchantId, UserType::MERCHANT);

        /** @var User $response */
        $response = PayFacService::addFunds()
            ->withAmount($amount)
            ->withAccountNumber($accountId)
            ->withUserReference($merchant->userReference)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("DECLINED", $response->responseCode);
        $this->assertNotNull($response->fundsAccountDetails);
        $this->assertEquals(FundsStatus::DECLINE, $response->fundsAccountDetails->status);
        $this->assertEquals($amount, $response->fundsAccountDetails->amount);
        $this->assertEquals('DEBIT', $response->fundsAccountDetails->paymentMethodType);
        $this->assertEquals('BANK_TRANSFER', $response->fundsAccountDetails->paymentMethodName);
        $this->assertNotNull($response->fundsAccountDetails->account);
        $this->assertEquals($accountId, $response->fundsAccountDetails->account->id);
    }

    public function testAddFunds_WithoutAmount()
    {
        $currency = "USD";
        $accountId = "FMA_a78b841dfbd14803b3a31e4e0c514c72";
        $merchantId = 'MER_5096d6b88b0b49019c870392bd98ddac';
        $merchant = User::fromId($merchantId, UserType::MERCHANT);

        $errorFound = false;
        try {
            PayFacService::addFunds()
                ->withAccountNumber($accountId)
                ->withUserReference($merchant->userReference)
                ->withPaymentMethodName(PaymentMethodName::BANK_TRANSFER)
                ->withPaymentMethodType(PaymentMethodType::CREDIT)
                ->withCurrency($currency)
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAddFunds_WithoutAccountNumber()
    {
        $amount = "10";
        $currency = "USD";
        $merchantId = 'MER_5096d6b88b0b49019c870392bd98ddac';
        $merchant = User::fromId($merchantId, UserType::MERCHANT);

        $errorFound = false;
        try {
            PayFacService::addFunds()
                ->withAmount($amount)
                ->withUserReference($merchant->userReference)
                ->withPaymentMethodName(PaymentMethodName::BANK_TRANSFER)
                ->withPaymentMethodType(PaymentMethodType::CREDIT)
                ->withCurrency($currency)
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('accountNumber cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAddFunds_WithoutUserRef()
    {
        $amount = "10";
        $currency = "USD";
        $accountId = "FMA_a78b841dfbd14803b3a31e4e0c514c72";

        $errorFound = false;
        try {
            PayFacService::addFunds()
                ->withAmount($amount)
                ->withAccountNumber($accountId)
                ->withPaymentMethodName(PaymentMethodName::BANK_TRANSFER)
                ->withPaymentMethodType(PaymentMethodType::CREDIT)
                ->withCurrency($currency)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('property userId or config merchantId cannot be null for this transactionType', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    private function getAccountByType($merchantId, $type)
    {
        $response = ReportingService::findAccounts(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::MERCHANT_ID, $merchantId)
            ->andWith(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->execute();

        $this->assertNotEmpty($response->result);
        $index = array_search($type, array_column($response->result, 'type'));

        return ($index !== false ? $response->result[$index] : null);
    }

    private function getMerchants()
    {
        return ReportingService::findMerchants(1, 10)
            ->orderBy(MerchantAccountsSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::ACCOUNT_STATUS, MerchantAccountStatus::ACTIVE)
            ->andWith(SearchCriteria::START_DATE, $this->startDate)
            ->execute();
    }
}