<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PaymentType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class GpApiAchTest extends TestCase
{
    private $eCheck;

    private $address;

    private $customer;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '1234567890';
        $this->eCheck->routingNumber = '122000030';
        $this->eCheck->accountType = AccountType::SAVINGS;
        $this->eCheck->secCode = SecCode::WEB;
        $this->eCheck->checkReference = '123';
        $this->eCheck->merchantNotes = '123';
        $this->eCheck->bankName = 'First Union';
        $this->eCheck->checkHolderName = 'Jane Doe';

        $bankAddress = new Address();
        $bankAddress->streetAddress1 = "12000 Smoketown Rd";
        $bankAddress->streetAddress2 = "Apt 3B";
        $bankAddress->streetAddress3 = "no";
        $bankAddress->city = "Mesa";
        $bankAddress->postalCode = "22192";
        $bankAddress->state = "AZ";
        $bankAddress->countryCode = "US";
        $this->eCheck->bankAddress = $bankAddress;

        $this->address = new Address();
        $this->address->streetAddress1 = "Apartment 852";
        $this->address->streetAddress2 = "Complex 741";
        $this->address->streetAddress3 = "no";
        $this->address->city = "Chicago";
        $this->address->postalCode = "5001";
        $this->address->state = "IL";
        $this->address->countryCode = "US";

        $this->customer = new Customer();
        $this->customer->id = "e193c21a-ce64-4820-b5b6-8f46715de931";
        $this->customer->firstName = "James";
        $this->customer->lastName = "Mason";
        $this->customer->dateOfBirth = "1980-01-01";
        $this->customer->mobilePhone = new PhoneNumber('+35', '312345678', PhoneNumberType::MOBILE);
        $this->customer->homePhone = new PhoneNumber('+1', '12345899', PhoneNumberType::HOME);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testCheckSale()
    {
        $response = $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCheckRefund()
    {
        $response = $this->eCheck->refund(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCheckRefundExistingSale()
    {
        $this->markTestSkipped('GP-API sandbox limitation');
        $startDate = (new DateTime())->modify('-1 year');
        $endDate = (new DateTime())->modify('-2 days');

        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::END_DATE, $endDate)
                ->andWith(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::BANK_TRANSFER)
                ->andWith(SearchCriteria::PAYMENT_TYPE, PaymentType::SALE)
                ->andWith(DataServiceCriteria::AMOUNT, 11)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        $transactionSummary = reset($response->result);
        $this->assertNotNull($transactionSummary);
        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::ACH);

        $response = $transaction->refund()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCheckReauthorize()
    {
        $this->eCheck->secCode = SecCode::PPD;
        $this->eCheck->accountNumber = '051904524';
        $this->eCheck->routingNumber = '123456780';
        $startDate = (new \DateTime())->modify('-1 year');
        $endDate = (new \DateTime())->modify('-2 days');
        $amount = '1.29';
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->andWith(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::BANK_TRANSFER)
            ->andWith(SearchCriteria::PAYMENT_TYPE, PaymentType::SALE)
            ->andWith(DataServiceCriteria::AMOUNT, $amount)
            ->execute();

        $this->assertNotNull($response);
        if (count($response->result) > 0) {
            $this->assertNotEmpty($response->result);
            /** @var \GlobalPayments\Api\Entities\Reporting\TransactionSummary $transactionSummary */
            $transactionSummary = reset($response->result);
            $this->assertNotNull($transactionSummary);
            $this->assertEquals($amount, $transactionSummary->amount);
            $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::ACH);

            $response = $transaction->reauthorized()
                ->withDescription('Resubmitting ' . $transaction->referenceNumber)
                ->withBankTransferData($this->eCheck)
                ->execute();

            $this->assertNotNull($response);
            $this->assertEquals('SUCCESS', $response->responseCode);
        }
    }

    public function testCheckSaleThenRefund()
    {
        $response = $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $refund = $response->refund()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($refund);
        $this->assertEquals('SUCCESS', $refund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $refund->responseMessage);
        $this->assertEquals('A0000', $refund->authorizationCode);
    }

}