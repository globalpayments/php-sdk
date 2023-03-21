<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\PaymentMethodFunction;
use GlobalPayments\Api\Entities\Enums\PersonFunctions;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\StatusChangeReason;
use GlobalPayments\Api\Entities\Enums\UserStatus;
use GlobalPayments\Api\Entities\Enums\UserType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\PaymentStatistics;
use GlobalPayments\Api\Entities\Person;
use GlobalPayments\Api\Entities\PersonList;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\Entities\User;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\PayFacService;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Integration\Gateways\ProPay\TestData\TestAccountData;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class GpApiMerchantsOnboardTest extends TestCase
{
    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = "A1feRdMmEB6m0Y1aQ65H0bDi9ZeAEB2t";
        $config->appKey = "5jPt1OpB6LLitgi7";
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardNotPresent;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function testBoardMerchant()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $bankAccountInformation = $this->getBankAccountData();
        $paymentStatistics = $this->getPaymentStatistics();
        $creditCardInformation = TestAccountData::getCreditCardData();

        $idempotencyKey = GenerationUtils::getGuid();
        $merchant = PayFacService::createMerchant()
            ->withUserPersonalData($merchantData)
            ->withDescription('Merchant Business Description')
            ->withProductData($products)
            ->withCreditCardData($creditCardInformation, PaymentMethodFunction::PRIMARY_PAYOUT)
            ->withBankAccountData($bankAccountInformation, PaymentMethodFunction::SECONDARY_PAYOUT)
            ->withPersonsData($persons)
            ->withPaymentStatistics($paymentStatistics)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        /** @var User $merchant */
        $this->assertTrue($merchant instanceof User);
        $this->assertEquals("SUCCESS", $merchant->responseCode);
        $this->assertEquals(UserStatus::UNDER_REVIEW, $merchant->userStatus);
        $this->assertNotEmpty($merchant->userId);
    }

    public function testBoardMerchant_OnlyMandatory()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $merchant = PayFacService::createMerchant()
            ->withUserPersonalData($merchantData)
            ->withDescription('Merchant Business Description')
            ->withProductData($products)
            ->withPersonsData($persons)
            ->withPaymentStatistics($paymentStatistics)
            ->execute();

        /** @var User $merchant */
        $this->assertTrue($merchant instanceof User);
        $this->assertEquals("SUCCESS", $merchant->responseCode);
        $this->assertEquals(UserStatus::UNDER_REVIEW, $merchant->userStatus);
        $this->assertEquals($merchantData->userName, $merchant->name);
        $this->assertEquals("Merchant Boarding in progress", $merchant->statusDescription);
        $this->assertNotEmpty($merchant->userId);
    }

    //TODO - add idempotency key on service
    public function testBoardMerchant_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $merchant = PayFacService::createMerchant()
            ->withUserPersonalData($merchantData)
            ->withDescription('Merchant Business Description')
            ->withProductData($products)
            ->withPersonsData($persons)
            ->withPaymentStatistics($paymentStatistics)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        /** @var User $merchant */
        $this->assertTrue($merchant instanceof User);
        $this->assertEquals("SUCCESS", $merchant->responseCode);
        $this->assertEquals(UserStatus::UNDER_REVIEW, $merchant->userStatus);
        $this->assertEquals($merchantData->userName, $merchant->name);
        $this->assertEquals("Merchant Boarding in progress", $merchant->statusDescription);
        $this->assertNotEmpty($merchant->userId);

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
            $this->assertEquals('40039', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testGetMerchantInfo()
    {
        $merchantId = 'MER_98f60f1a397c4dd7b7167bda61520292';
        /** @var User $merchant */
        $merchant = PayFacService::getMerchantInfo($merchantId)->execute();
        $this->assertInstanceOf(User::class, $merchant);
        $this->assertNotNull($merchant->paymentMethodList);
        $paymentMethodList = $merchant->paymentMethodList->getIterator();
        if($paymentMethodList->valid()) {
            $paymentMethodList->seek(1);
            $this->assertInstanceOf(\GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod::class,
                $paymentMethodList->current()['payment_method'] );
        }
    }

    public function testGetMerchantInfo_RandomId()
    {
        $merchantId = 'MER_' . str_replace('-', '', GenerationUtils::getGuid());

        $errorFound = false;
        try {
            PayFacService::getMerchantInfo($merchantId)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - Merchant configuration does not exist for the following combination: MMA_1595ca59906346beae43d92c24863430 , " . $merchantId . "", $e->getMessage());
            $this->assertEquals('40041', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testGetMerchantInfo_InvalidId()
    {
        $errorFound = false;
        try {
            PayFacService::getMerchantInfo(GenerationUtils::getGuid())
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: INVALID_TRANSACTION_ACTION - Retrieve information about this transaction is not supported', $e->getMessage());
            $this->assertEquals('40042', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSearchMerchants()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 10)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertLessThanOrEqual(10, count($merchants->result));
    }

    public function testEditMerchantApplicantInfo()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 1)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertEquals(1, count($merchants->result));

        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);
        $persons = $this->getPersonList('Update');
        $response = $merchant->edit()
            ->withPersonsData($persons)
            ->execute();

        $this->assertTrue($response instanceof User);
        $this->assertEquals("PENDING", $response->responseCode);
    }

    public function testEditMerchantPaymentProcessing()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 1)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertEquals(1, count($merchants->result));
        $paymentStatistics = new PaymentStatistics();
        $paymentStatistics->totalMonthlySalesAmount = '1111';
        $paymentStatistics->highestTicketSalesAmount = '2222';

        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);
        $response = $merchant->edit()
            ->withPaymentStatistics($paymentStatistics)
            ->withDescription('Update merchant payment processing')
            ->execute();

        $this->assertTrue($response instanceof User);
        $this->assertEquals("PENDING", $response->responseCode);
    }

    public function testEditMerchantBusinessInformation()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 1)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertEquals(1, count($merchants->result));

        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);
        $merchant->userStatus = UserStatus::ACTIVE;

        $merchantData = new UserPersonalData();
        $merchantData->userName = 'Username';
        $merchantData->dba = 'Doing Business As';
        $merchantData->website = 'https://abcd.com';
        $merchantData->taxIdReference = '987654321';
        $businessAddress = new Address();
        $businessAddress->streetAddress1 = "Apartment 852";
        $businessAddress->streetAddress2 = "Complex 741";
        $businessAddress->streetAddress3 = "Unit 4";
        $businessAddress->city = "Chicago";
        $businessAddress->state = "IL";
        $businessAddress->postalCode = "50001";
        $businessAddress->countryCode = "840";
        $merchantData->userAddress = $businessAddress;

        /** @var User $response */
        $response = $merchant->edit()
            ->withUserPersonalData($merchantData)
            ->withDescription('Sample Data for description')
            ->execute();

        $this->assertTrue($response instanceof User);
        $this->assertEquals("PENDING", $response->responseCode);
        $this->assertEquals(UserStatus::UNDER_REVIEW, $response->userStatus);
        $this->assertEquals($merchantData->userName, $response->name);
    }

    public function testEditMerchant_RemoveMerchantFromPartner_FewArguments()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 1)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertEquals(1, count($merchants->result));

        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $errorFound = false;
        try {
            $merchant->edit()
                ->withStatusChangeReason(StatusChangeReason::REMOVE_PARTNERSHIP)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Required field is missing.', $e->getMessage());
            $this->assertEquals('40241', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testEditMerchant_RemoveMerchantFromPartner_TooManyArguments()
    {
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $merchants */
        $merchants = ReportingService::findMerchants(1, 1)->execute();

        $this->assertGreaterThan(0, $merchants->totalRecordCount);
        $this->assertEquals(1, count($merchants->result));

        $merchant = User::fromId(reset($merchants->result)->id, UserType::MERCHANT);

        $errorFound = false;
        try {
            $merchant->edit()
                ->withUserPersonalData($this->getMerchantData())
                ->withStatusChangeReason(StatusChangeReason::REMOVE_PARTNERSHIP)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Bad Request. The request has extra tags which are not required.', $e->getMessage());
            $this->assertEquals('40268', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutMerchantData()
    {
        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (ArgumentException $e) {
            $errorFound = true;
            $this->assertEquals('Merchant data is mandatory!', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutUserName()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->userName = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutLegalName()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->legalName = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields legal_name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutMerchantType()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->type = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();


        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields type', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutDba()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->dba = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields dba', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutWebsite()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->website = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields website', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutTaxIdReference()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->taxIdReference = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields tax_id_reference', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutNotificationStatusUrl()
    {
        $merchantData = $this->getMerchantData();
        $merchantData->notificationStatusUrl = null;

        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields notifications.status_url', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutPersons()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields : email', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutPaymentStatistics()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withDescription('Merchant Business Description')
                ->withProductData($products)
                ->withPersonsData($persons)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_processing_statistics.total_monthly_sales_amount', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutTotalMonthlySalesAmount()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();

        $paymentStatistics = new PaymentStatistics();
        $paymentStatistics->averageTicketSalesAmount = '50000';
        $paymentStatistics->highestTicketSalesAmount = '60000';

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withProductData($products)
                ->withDescription('Merchant Business Description')
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_processing_statistics.total_monthly_sales_amount', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutAverageTicketSalesAmount()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();

        $paymentStatistics = new PaymentStatistics();
        $paymentStatistics->totalMonthlySalesAmount = '3000000';
        $paymentStatistics->highestTicketSalesAmount = '60000';

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withProductData($products)
                ->withDescription('Merchant Business Description')
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_processing_statistics.average_ticket_sales_amount', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutHighestTicketSalesAmount()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();

        $paymentStatistics = new PaymentStatistics();
        $paymentStatistics->totalMonthlySalesAmount = '3000000';
        $paymentStatistics->averageTicketSalesAmount = '50000';

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withProductData($products)
                ->withDescription('Merchant Business Description')
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_processing_statistics.highest_ticket_sales_amount', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBoardMerchant_WithoutDescription()
    {
        $merchantData = $this->getMerchantData();
        $products = $this->getProductList();
        $persons = $this->getPersonList();
        $paymentStatistics = $this->getPaymentStatistics();

        $errorFound = false;
        try {
            PayFacService::createMerchant()
                ->withUserPersonalData($merchantData)
                ->withProductData($products)
                ->withPersonsData($persons)
                ->withPaymentStatistics($paymentStatistics)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields description', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    private function getMerchantData()
    {
        $merchantData = new UserPersonalData();
        $merchantData->userName = 'CERT_Propay_' . (new \DateTime())->format("YmdHis");
        $merchantData->legalName = 'Business Legal Name';
        $merchantData->dba = 'Doing Business As';
        $merchantData->merchantCategoryCode = '5999';
        $merchantData->website = 'https://example.com';
        $merchantData->notificationEmail = 'merchant@example.com';
        $merchantData->currencyCode = 'USD';
        $merchantData->taxIdReference = '123456789';
        $merchantData->tier = 'test';
        $merchantData->type = UserType::MERCHANT;

        $businessAddress = new Address();
        $businessAddress->streetAddress1 = "Apartment 852";
        $businessAddress->streetAddress2 = "Complex 741";
        $businessAddress->streetAddress3 = "Unit 4";
        $businessAddress->city = "Chicago";
        $businessAddress->state = "IL";
        $businessAddress->postalCode = "50001";
        $businessAddress->countryCode = "840";

        $merchantData->userAddress = $businessAddress;

        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = "Flat 456";
        $shippingAddress->streetAddress2 = "House 789";
        $shippingAddress->streetAddress3 = "Basement Flat";
        $shippingAddress->city = "Halifax";
        $shippingAddress->postalCode = "W5 9HR";
        $shippingAddress->countryCode = "826";

        $merchantData->mailingAddress = $shippingAddress;
        $merchantData->notificationStatusUrl = 'https://www.example.com/notifications/status';

        return $merchantData;
    }

    private function getProductList()
    {
        $products = [
            'PRO_TRA_CP-US-CARD-A920_SP',
            'PRO_FMA_PUSH-FUNDS_PP',
            'PRO_TRA_CNP_US_BANK-TRANSFER_PP',
            'PRO_TRA_CNP-US-CARD_PP'
        ];
        foreach ($products as $prodId) {
            $product = new Product();
            $product->productId = $prodId;
            $productData[] = $product;
        }

        return $productData;
    }

    private function getPersonList($type = '')
    {
        $person = new Person();
        $person->functions = PersonFunctions::APPLICANT;
        $person->firstName = 'James ' . $type;
        $person->middleName = 'Mason ' . $type;
        $person->lastName = 'Doe ' . ' ' . $type;
        $person->email = 'uniqueemail@address.com';
        $person->dateOfBirth = date('1982-02-23');
        $person->nationalIdReference = '123456789';
        $person->jobTitle = 'CEO';
        $person->equityPercentage = '25';
        if (empty($type)) {
            $person->address = new Address();
            $person->address->streetAddress1 = '1 Business Address';
            $person->address->streetAddress2 = 'Suite 2';
            $person->address->streetAddress3 = '1234';
            $person->address->city = 'Atlanta';
            $person->address->state = 'GA';
            $person->address->postalCode = '30346';
            $person->address->country = 'US';
        }
        $person->homePhone = new PhoneNumber('01', '8008675309', PhoneNumberType::HOME);
        $person->workPhone = new PhoneNumber('01', '8008675309', PhoneNumberType::WORK);
        $persons = new PersonList();
        $persons->append($person);

        return $persons;
    }

    private function getBankAccountData()
    {
        $bankAccountInformation = new BankAccountData();
        $bankAccountInformation->accountHolderName = 'Bank Account Holder Name';
        $bankAccountInformation->accountNumber = '123456788';
        $bankAccountInformation->accountOwnershipType = 'Personal';
        $bankAccountInformation->accountType = AccountType::SAVINGS;
        $bankAccountInformation->routingNumber = '102000076';

        return $bankAccountInformation;
    }

    private function getPaymentStatistics()
    {
        $paymentStatistics = new PaymentStatistics();
        $paymentStatistics->totalMonthlySalesAmount = '3000000';
        $paymentStatistics->averageTicketSalesAmount = '50000';
        $paymentStatistics->highestTicketSalesAmount = '60000';

        return $paymentStatistics;
    }
}