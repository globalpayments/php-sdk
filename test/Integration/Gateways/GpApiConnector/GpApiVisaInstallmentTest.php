<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\{Address, InstallmentData, InstallmentTerms};
use GlobalPayments\Api\PaymentMethods\Installment;
use GlobalPayments\Api\Entities\Enums\{Channel, Environment, TransactionStatus};
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
        $config = new GpApiConfig();
        $config->appId = 'hkjrcsGDhWiDt8GEhoDMKy3pzFz5R0Bo';
        $config->appKey = 'cQOKHoAAvNIcEN8s';
        $config->channel = Channel::CardNotPresent;
        $config->environment = Environment::TEST;
        $config->serviceUrl = 'https://apis.sandbox.boipagateway.com/ucp';
        
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'GPECOM_Installments_Processing';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new RequestConsoleLogger(new \GlobalPayments\Api\Utils\Logging\Logger("logs"));
        
        return $config;
    }

    /** Installment query config - uses globalpay.com */
    public function setUpInstallmentConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = 'hkjrcsGDhWiDt8GEhoDMKy3pzFz5R0Bo';
        $config->appKey = 'cQOKHoAAvNIcEN8s';
        $config->channel = Channel::CardNotPresent;
        $config->environment = Environment::TEST;
        $config->serviceUrl = 'https://apis.sandbox.globalpay.com/ucp';
        
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'GPECOM_Installments_Processing';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new RequestConsoleLogger(new \GlobalPayments\Api\Utils\Logging\Logger("logs"));
        
        return $config;
    }

    /** Helper: Create Installment query object */
    private function createInstallmentQuery(string $amount = '10000', string $currency = 'GBP'): Installment
    {
        $installment = new Installment();
        $installment->accountName = 'GPECOM_Installments_Processing';
        $installment->channel = 'CNP';
        $installment->amount = $amount;
        $installment->currency = $currency;
        $installment->country = 'GB';
        $installment->program = 'VIS';
        $installment->reference = 'QUERY-' . GenerationUtils::getGuid();
        $installment->funding_mode = 'CONSUMER_FUNDED';
        $installment->eligible_plans = 'LIMITED';
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

    /** Helper: Create HPP payer object */
    private function createHPPPayer(): \stdClass
    {
        $payer = new \stdClass();
        $payer->email = 'james.mason@example.com';
        $payer->firstName = 'James';
        $payer->lastName = 'Mason';
        $payer->name = 'James Mason';
        $payer->language = 'en';
        $payer->mobilePhone = null;
        $payer->shippingAddress = null;
        $payer->shippingPhone = null;
        
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = '123 Bill Street';
        $billingAddress->city = 'London';
        $billingAddress->postalCode = 'SW1A 1AA';
        $billingAddress->countryCode = 'GB';
        $payer->billingAddress = $billingAddress;
        
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
        $transactionConfig->allowedPaymentMethods = ['CARD'];
        $order->HPPTransactionConfiguration = $transactionConfig;
        $order->HPPPaymentMethodConfiguration = null;
        
        return $order;
    }

    /** Test transaction with VIS program */
    public function testCreditSaleWithVisProgram()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData())
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        
        if (!empty($response->installment)) {
            $this->assertEquals('VIS', $response->installment->program);
        }
    }

    /** Test POST /installments query */
    public function testQueryInstallmentPlans()
    {
        $response = $this->createInstallmentQuery()->create('installments');
        
        $this->assertNotNull($response);
        $this->assertNotEmpty($response->id);
        $this->assertIsArray($response->terms);
        $this->assertNotEmpty($response->terms);
    }

    /** Test complete flow: Query → Transaction → GET */
    public function testCompleteInstallmentFlow()
    {
        $queryResponse = $this->createInstallmentQuery()->create('installments');
        $this->assertNotNull($queryResponse);
        $this->assertNotEmpty($queryResponse->id);
        
        $transactionResponse = $this->visaCard->charge(100.00)
            ->withCurrency('GBP')
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData('VIS', $queryResponse->id))
            ->withAllowDuplicates(true)
            ->execute();
        
        $this->assertNotNull($transactionResponse);
        $this->assertEquals('SUCCESS', $transactionResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transactionResponse->responseMessage);
        
        if (!empty($transactionResponse->installment)) {
            $this->assertNotEmpty($transactionResponse->installment->id);
            $this->assertEquals('VIS', $transactionResponse->installment->program);
        }
        sleep(2);
        
        $installmentDetails = \GlobalPayments\Api\Services\InstallmentService::get($queryResponse->id, 'installments');
        $this->assertNotNull($installmentDetails);
        $this->assertNotEmpty($installmentDetails->id);
        $this->assertEquals($queryResponse->id, $installmentDetails->id);
    }

    /** Test GET /transactions includes installment data */
    public function testGetTransactionWithInstallment()
    {
        $queryResponse = $this->createInstallmentQuery()->create('installments');
        $this->assertNotNull($queryResponse);
        $this->assertNotNull($queryResponse->id);
        
        $transactionResponse = $this->visaCard->charge(100)
            ->withCurrency('GBP')
            ->withAddress($this->address)
            ->withInstallment($this->createInstallmentData('VIS', $queryResponse->id))
            ->execute();
        
        $this->assertNotNull($transactionResponse);
        $this->assertEquals('SUCCESS', $transactionResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transactionResponse->responseMessage);
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
        $terms->max_amount = 200000;
        $installmentData->terms = $terms;
        
        $hostedPaymentData = new \GlobalPayments\Api\Entities\HostedPaymentData();
        $hostedPaymentData->type = 'HOSTED_PAYMENT_PAGE';
        $hostedPaymentData->name = 'Visa Installment Payment Test';
        $hostedPaymentData->description = 'Test HPP with Visa Installments';
        $hostedPaymentData->reference = 'HPP-' . GenerationUtils::getGuid();
        $hostedPaymentData->payer = $this->createHPPPayer();
        $hostedPaymentData->order = $this->createHPPOrder('200000', 'EUR');
        
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

    /** Test installment query with missing required fields */
    public function testQueryInstallmentWithMissingCardDetails()
    {
        $this->expectException(\Exception::class);
        
        $installment = new Installment();
        $installment->accountName = 'GPECOM_Installments_Processing';
        $installment->amount = '10000';
        $installment->currency = 'GBP';
        $installment->program = 'VIS';
        // Missing card details - should fail
        
        $installment->create('installments');
    }

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
        
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNull($response->installment);
    }
}
