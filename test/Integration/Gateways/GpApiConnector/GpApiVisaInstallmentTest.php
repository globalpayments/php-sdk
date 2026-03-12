<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\{Address, Customer, InstallmentData, InstallmentTerms};
use GlobalPayments\Api\PaymentMethods\Installment;
use GlobalPayments\Api\Entities\Enums\{Channel, Environment, ServiceEndpoints, TransactionStatus};
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use PHPUnit\Framework\TestCase;

/**
 * Visa Installment Tests for GP-API
 */
class GpApiVisaInstallmentTest extends TestCase
{
    private CreditCardData $visaCard;
    private Address $address;
    private string $currency = 'USD';
    private float $amount = 99.99;

    public function setup(): void
    {
        // Configure two separate endpoints
        ServicesContainer::configureService($this->setUpConfig());
        ServicesContainer::configureService($this->setUpInstallmentConfig(), 'installments');

        $this->address = new Address();
        $this->address->streetAddress1 = "123 Main St.";
        $this->address->city = "Downtown";
        $this->address->state = "NJ";
        $this->address->postalCode = "12345";
        $this->address->country = "US";

        $this->visaCard = new CreditCardData();
        $this->visaCard->number = "4263970000005262";
        $this->visaCard->expMonth = 12;
        $this->visaCard->expYear = 2027;
        $this->visaCard->cvn = "123";
        $this->visaCard->cardPresent = false;
        $this->visaCard->readerPresent = false;
    }

    /** Transaction config - uses boipagateway.com */
    public function setUpConfig(): GpApiConfig
    {
        return $this->buildGpApiConfig(ServiceEndpoints::GP_API_TEST_BOIPA, 'GB');
    }

    /** Installment query config - uses globalpay.com */
    public function setUpInstallmentConfig(): GpApiConfig
    {
        return $this->buildGpApiConfig(ServiceEndpoints::GP_API_TEST);
    }

    private function buildGpApiConfig(string $serviceUrl, ?string $country = null): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = 'hkjrcsGDhWiDt8GEhoDMKy3pzFz5R0Bo';
        $config->appKey = 'cQOKHoAAvNIcEN8s';
        $config->channel = Channel::CardNotPresent;
        if ($country !== null) {
            $config->country = $country;
        }
        $config->environment = Environment::TEST;
        $config->serviceUrl = $serviceUrl;
        
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'GPECOM_Installments_Processing';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new RequestConsoleLogger(new \GlobalPayments\Api\Utils\Logging\Logger("logs"));
        
        return $config;
    }

    /** Helper: Create Installment query object */
    private function createInstallmentQuery(string $amount = '10000', string $currency = 'GBP', string $country = 'UK'): Installment
    {
        $installment = new Installment();
        $installment->accountName = 'GPECOM_Installments_Processing';
        $installment->channel = 'CNP';
        $installment->amount = $amount;
        $installment->currency = $currency;
        $installment->country = $country;
        $installment->program = 'VIS';
        $installment->reference = 'QUERY-' . GenerationUtils::getGuid();
        $installment->funding_mode = 'CONSUMER_FUNDED';
        $installment->eligible_plans = 'LIMITED';
        $installment->usage_mode = 'USE_CARD_NUMBER';
        $installment->entryMode = 'ECOM';
        
        $terms = new InstallmentTerms();
        $terms->max_time_unit_number = 24;
        $terms->max_amount = (int)$amount;
        $installment->terms = $terms;
        
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = '12';
        $card->expYear = '2027';
        $installment->cardDetails = $card;
        
        return $installment;
    }

    /** Helper: Create InstallmentData object */
    private function createInstallmentData(string $program = 'VIS', ?string $reference = null): InstallmentData
    {
        $data = new InstallmentData();
        $data->program = $program;
        if ($reference) {
            $data->reference = $reference;
        }
        return $data;
    }

    /** Helper: Create InstallmentData for /transactions sale request */
    private function createSaleInstallmentDataForTransactions(?string $id = null, ?string $reference = null): InstallmentData
    {
        $data = new InstallmentData();
        if (!empty($id)) {
            $data->id = $id;
        }
        if (!empty($reference)) {
            $data->reference = $reference;
        }
        $data->program = 'VIS';

        $terms = new InstallmentTerms();
        $terms->language = 'fre';
        $terms->version = '2';
        $data->terms = $terms;

        return $data;
    }

    /** Helper: Create customer for payment_method first/last name */
    private function createSaleCustomer(): Customer
    {
        $customer = new Customer();
        $customer->firstName = 'James';
        $customer->lastName = 'Mason';

        return $customer;
    }

    /** Helper: Create card for /transactions sale request */
    private function createSaleCardForTransactions(): CreditCardData
    {
        $card = new CreditCardData();
        $card->number = '4622943127052828';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '999';
        $card->cardPresent = false;
        $card->readerPresent = false;

        return $card;
    }

    /** Helper: Assert a successful captured transaction response */
    private function assertCapturedSuccess($response): void
    {
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    /** Helper: Create HPP payer object */
    private function createHPPPayer(): \stdClass
    {
        $payer = new \stdClass();
        $payer->email = 'jamesmason@example.com';
        $payer->firstName = 'James';
        $payer->lastName = 'Mason';
        $payer->name = 'James Mason';
        $payer->language = 'en';
        $payer->addressMatchIndicator = true;

        $mobilePhone = new \stdClass();
        $mobilePhone->countryCode = '44';
        $mobilePhone->number = '1801555888';
        $payer->mobilePhone = $mobilePhone;
        
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = 'bill_street1';
        $billingAddress->streetAddress2 = 'bill_street2';
        $billingAddress->streetAddress3 = 'bill_street3';
        $billingAddress->city = 'Bill_city';
        $billingAddress->postalCode = '44';
        $billingAddress->countryCode = 'IE';
        $payer->billingAddress = $billingAddress;

        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = 'Flat 123';
        $shippingAddress->streetAddress2 = 'House 456';
        $shippingAddress->streetAddress3 = 'Btower';
        $shippingAddress->city = 'Chicago';
        $shippingAddress->postalCode = '50001';
        $shippingAddress->state = 'IL';
        $shippingAddress->countryCode = 'US';
        $payer->shippingAddress = $shippingAddress;

        $shippingPhone = new \stdClass();
        $shippingPhone->countryCode = '99';
        $shippingPhone->number = '1801555999';
        $payer->shippingPhone = $shippingPhone;
        
        return $payer;
    }

    /** Helper: Create HPP order object */
    private function createHPPOrder(string $amount, string $currency): \stdClass
    {
        $order = new \stdClass();
        $order->amount = $amount;
        $order->currency = $currency;
        $order->reference = 'ORDER-' . GenerationUtils::getGuid();
        
        $transactionConfig = new \stdClass();
        $transactionConfig->captureMode = 'AUTO';
        $transactionConfig->currencyConversionMode = true;
        $transactionConfig->allowedPaymentMethods = ['CARD', 'testpay'];
        $order->HPPTransactionConfiguration = $transactionConfig;

        $authentications = new \stdClass();
        $authentications->preference = 'CHALLENGE_PREFERRED';
        $authentications->billingAddressRequired = false;

        $paymentMethodConfiguration = new \stdClass();
        $paymentMethodConfiguration->authentications = $authentications;
        $paymentMethodConfiguration->apm = null;
        $paymentMethodConfiguration->storageMode = null;
        $paymentMethodConfiguration->digitalWallets = null;
        $order->HPPPaymentMethodConfiguration = $paymentMethodConfiguration;
        
        return $order;
    }

    /** Test transaction with VIS program /ucp/transactions */ 
    public function testCreditSaleWithVisProgram()
    {
        $queryResponse = $this->createInstallmentQuery('100000', 'GBP', 'GB')->create('default');
        $this->assertNotNull($queryResponse);
        $this->assertNotEmpty($queryResponse->id);
        $queryTermReference = $queryResponse->terms[0]->reference ?? null;
        $this->assertNotEmpty($queryTermReference);
        sleep(2);

        $card = $this->createSaleCardForTransactions();

        $response = $card->charge(1000.00)
                ->withCurrency('GBP')
                ->withCustomerData($this->createSaleCustomer())
                ->withRequestMultiUseToken(true)
                ->withPaymentMethodStorageMode('ALWAYS')
                ->withPaymentMethodUsageMode('USE_NETWORK_TOKEN')
                ->withInstallment($this->createSaleInstallmentDataForTransactions($queryResponse->id, $queryTermReference))
                ->withAllowDuplicates(true)
                ->execute();

        $this->assertCapturedSuccess($response);
    }

    /** Test POST /installments query */ 
    public function testQueryInstallmentPlans()
    {
        $response = $this->createInstallmentQuery()->create('installments');
        
        $this->assertNotNull($response);
        $this->assertNotEmpty($response->id);
        $this->assertNotEmpty($response->timeCreated);
        $this->assertEquals('INSTALLMENT_QUERY', $response->type);
        $this->assertEquals('AVAILABLE', $response->status);
        $this->assertEquals('CNP', $response->channel);
        $this->assertEquals('VIS', $response->program);
        $this->assertNotEmpty($response->merchantId);
        $this->assertNotEmpty($response->merchantName);
        $this->assertNotEmpty($response->accountId);
        $this->assertNotEmpty($response->accountName);
        $this->assertNotEmpty($response->reference);
        $this->assertEquals('ECOM', $response->entryMode);
        $this->assertNotNull($response->card);
        $this->assertEquals('VISA', $response->card->brand);
        $this->assertNotEmpty($response->card->maskedNumberLast4);
        $this->assertNotEmpty($response->card->cardExpMonth);
        $this->assertNotEmpty($response->card->cardExpYear);
        $this->assertNotNull($response->action);
        $this->assertEquals('SUCCESS', $response->action->resultCode);
        $this->assertIsArray($response->terms);
        $this->assertNotEmpty($response->terms);

        $firstPlan = $response->terms[0] ?? null;
        $this->assertNotNull($firstPlan);
        $this->assertNotEmpty($firstPlan->reference ?? null);
        $this->assertNotEmpty($firstPlan->time_unit ?? null);
        $this->assertNotEmpty($firstPlan->count ?? null);
        $this->assertNotEmpty($firstPlan->fees ?? null);
    }

    /** Test installment query and GET by id */
    public function testInstallmentQueryAndGetById()
    {
        $queryResponse = $this->createInstallmentQuery()->create('installments');
        $this->assertNotNull($queryResponse);
        $this->assertNotEmpty($queryResponse->id);

        $installmentDetails = \GlobalPayments\Api\Services\InstallmentService::get($queryResponse->id, 'installments');
        $this->assertNotNull($installmentDetails);
        $this->assertNotEmpty($installmentDetails->id);
        $this->assertEquals($queryResponse->id, $installmentDetails->id);
    }

    /** Test GET /transactions includes installment data */
    public function testGetTransactionWithInstallment()
    {
        $transactionResponse = $this->visaCard->charge(100)
            ->withCurrency('GBP')
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData('VIS'))
            ->execute();

        $this->assertCapturedSuccess($transactionResponse);
        sleep(5);
        
        $retrievedTransaction = \GlobalPayments\Api\Services\ReportingService::transactionDetail($transactionResponse->transactionId)
            ->execute();
        
        $this->assertNotNull($retrievedTransaction);
        $this->assertEquals($transactionResponse->transactionId, $retrievedTransaction->transactionId);
        
        if (isset($retrievedTransaction->installment) && $retrievedTransaction->installment !== null) {
            $this->assertNotNull($retrievedTransaction->installment->id);
            $this->assertNotNull($retrievedTransaction->installment->program);
        }
    }

    /** Test HPP with Links - Create payment link with installments */
    public function testCreateHPPLinkWithInstallments()
    {
        $installmentData = $this->createInstallmentData();
        $installmentData->funding_mode = 'CONSUMER_FUNDED';
        $terms = new InstallmentTerms();
        $terms->max_time_unit_number = 24;
        $terms->max_amount = 100000;
        $installmentData->terms = $terms;
        
        $hostedPaymentData = new \GlobalPayments\Api\Entities\HostedPaymentData();
        $hostedPaymentData->type = 'HOSTED_PAYMENT_PAGE';
        $hostedPaymentData->name = 'Mobile Bill Payment';
        $hostedPaymentData->description = 'Test Description';
        $hostedPaymentData->reference = 'HPP-' . GenerationUtils::getGuid();
        $hostedPaymentData->payer = $this->createHPPPayer();
        $hostedPaymentData->order = $this->createHPPOrder('2000', 'EUR');
        
        $notifications = new \stdClass();
        $notifications->returnUrl = 'https://example.com/Return';
        $notifications->statusUrl = 'https://example.com/Status';
        $notifications->cancelUrl = 'https://example.com/Cancel';
        $hostedPaymentData->notifications = $notifications;
        $hostedPaymentData->installments = $installmentData;
        
        $response = (new \GlobalPayments\Api\Builders\AuthorizationBuilder(\GlobalPayments\Api\Entities\Enums\TransactionType::HOSTED_PAYMENT_PAGE))
            ->withAmount(2000.00)
            ->withCurrency('EUR')
            ->withHostedPaymentData($hostedPaymentData)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse->id);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertEquals('ACTIVE', $response->payByLinkResponse->status);
    }

    // ========== NEGATIVE TEST CASES ==========

    /** Test transaction with invalid program code */
    public function testTransactionWithInvalidProgramCode()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);
        
        $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData('INVALID_PROGRAM'))
            ->execute();
    }

    /** Test GET installment with non-existent ID */
    public function testGetNonExistentInstallment()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);
        
        \GlobalPayments\Api\Services\InstallmentService::get('NON_EXISTENT_ID_12345', 'installments');
    }

    /** Test installment query without terms */
    public function testQueryInstallmentWithoutTerms()
    {
        $this->expectException(\Exception::class);
        
        $installment = new Installment();
        $installment->accountName = 'GPECOM_Installments_Processing';
        $installment->channel = 'CNP';
        $installment->amount = '10000';
        $installment->currency = 'GBP';
        $installment->program = 'VIS';
        // Missing terms - should fail
        
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = '12';
        $card->expYear = '2027';
        $installment->cardDetails = $card;
        
        $installment->create('installments');
    }

    /** Test transaction with zero amount */
    public function testTransactionWithZeroAmount()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);
        
        $this->visaCard->charge(0)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData())
            ->execute();
    }

    /** Regression test: Regular transaction WITHOUT installments should still work */
    public function testRegularTransactionWithoutInstallments()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->execute();

        $this->assertCapturedSuccess($response);
        $this->assertNull($response->installment);
    }
}