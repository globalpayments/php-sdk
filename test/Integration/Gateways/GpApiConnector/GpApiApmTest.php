<?php

namespace Gateways\GpApiConnector;

use DateTime;
use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiAuthorizationRequestBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\PayerDetails;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Terms;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\CaptureMode;
use GlobalPayments\Api\Entities\Enums\CashpressoPaymentPlan;
use GlobalPayments\Api\Entities\Enums\CashpressoShippingMethod;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DataResidency;
use GlobalPayments\Api\Entities\Enums\HPPAllowedPaymentMethods;
use GlobalPayments\Api\Entities\Enums\MerchantCategory;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\OrderDetails;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\HPPService;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use PHPUnit\Framework\TestCase;

class GpApiApmTest extends TestCase
{
    private AlternativePaymentMethod $paymentMethod;
    private string $currency;
    private Address $shippingAddress;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::PAYPAL);

        $this->paymentMethod->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->cancelUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->descriptor = 'Test Transaction';
        $this->paymentMethod->country = 'GB';
        $this->paymentMethod->accountHolderName = 'James Mason';

        $this->currency = 'USD';

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Apartment 852';
        $this->shippingAddress->streetAddress2 = 'Complex 741';
        $this->shippingAddress->streetAddress3 = 'no';
        $this->shippingAddress->city = 'Chicago';
        $this->shippingAddress->postalCode = '5001';
        $this->shippingAddress->state = 'IL';
        $this->shippingAddress->countryCode = 'US';
    }

    public function setUpConfig(): GpApiConfig
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->requestLogger = new RequestConsoleLogger();

        return $config;
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    private function createEratyPaymentMethod(): AlternativePaymentMethod
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ERATY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->cancelUrl = 'https://example.com/cancelUrl';
        $paymentMethod->country = 'PL';
        $paymentMethod->accountHolderName = 'John Doe';
        $paymentMethod->category = 'BNPL';
        $paymentMethod->terms = new Terms();
        $paymentMethod->terms->time_unit = 'MONTH';
        $paymentMethod->terms->count = '6';
        $paymentMethod->terms->mode = 'BANK_INTEREST';

        return $paymentMethod;
    }

    private function configureEratyService(): void
    {
        $config = $this->setUpConfig();
        $config->appId = '';
        $config->appKey = '';
        $config->country = 'PL';
        $config->accessTokenInfo->transactionProcessingAccountName = 'GPECOM_APM_Transaction_Processing';
        $config->requestLogger = new RequestConsoleLogger();
        ServicesContainer::configureService($config);
    }

    private function configureCashpressoService(string $country = 'DE'): GpApiConfig
    {
        $config = $this->setUpConfig();
        $config->appId = '';
        $config->appKey = '';
        $config->dataResidency = DataResidency::EU;
        $config->serviceUrl = 'https://apis-qa.globalpay.com/ucp';
        $config->country = $country;
        $config->accessTokenInfo->transactionProcessingAccountName = 'GPECOM_CASHPRESSO_APM_Transaction_Processing';
        $config->requestLogger = new RequestConsoleLogger();
        ServicesContainer::configureService($config);

        return $config;
    }

    private function createCashpressoPaymentMethod(string $paymentPlan, string $country = 'DE'): AlternativePaymentMethod
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::CASHPRESSO);
        $paymentMethod->returnUrl = 'https://webhook.site/return';
        $paymentMethod->statusUpdateUrl = 'https://webhook.site/status';
        $paymentMethod->cancelUrl = 'https://webhook.site/cancel';
        $paymentMethod->country = $country;
        $paymentMethod->accountHolderName = 'James Mason';
        $paymentMethod->paymentPlan = $paymentPlan;

        return $paymentMethod;
    }

    private function configureBlikLevelZeroService(): void
    {
        $config = $this->setUpConfig();
        $config->appId = '';
        $config->appKey = '';
        $config->environment = GpApiConfig::QA_ENVIRONMENT;
        $config->dataResidency = DataResidency::EU;
        $config->serviceUrl = 'https://apis-qa.globalpay.com/ucp';
        $config->country = 'PL';
        $config->accessTokenInfo->transactionProcessingAccountName = 'GPECOM_BLIK_APM_Transaction_Processing';
        $config->requestLogger = new RequestConsoleLogger();
        ServicesContainer::configureService($config);
    }

    private function createBlikLevelZeroPaymentMethod(): AlternativePaymentMethod
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::BLIK);
        $paymentMethod->returnUrl = 'https://webhook.site/5ef888b0-a200-403d-97c1-6e19f698ea98';
        $paymentMethod->statusUpdateUrl = 'https://webhook.site/5ef888b0-a200-403d-97c1-6e19f698ea98';
        $paymentMethod->cancelUrl = 'https://webhook.site/5ef888b0-a200-403d-97c1-6e19f698ea98';
        $paymentMethod->country = 'PL';
        $paymentMethod->accountHolderName = 'James2 Carl';
        $paymentMethod->mode = 'level_zero';
        $paymentMethod->paymentCodeInitiator = 'payer';
        $paymentMethod->paymentCode = '999000';

        return $paymentMethod;
    }

    private function createCashpressoCustomer(): Customer
    {
        $customer = new Customer();
        $customer->firstName = 'James';
        $customer->lastName = 'Mason';
        $customer->email = 'james.mason@example.com';

        return $customer;
    }

    private function createCashpressoBillingAddress(string $country = 'DE'): Address
    {
        $billingAddress = new Address();
        $billingAddress->streetAddress1 = 'Marienplatz 8';
        $billingAddress->streetAddress2 = 'Suite 302, Commercial Center';
        $billingAddress->streetAddress3 = 'Old Town District';
        $billingAddress->city = $country === 'AT' ? 'Wien' : 'Muenchen';
        $billingAddress->postalCode = $country === 'AT' ? '1010' : '80331';
        $billingAddress->state = $country === 'AT' ? 'WI' : 'BY';
        $billingAddress->countryCode = $country;

        return $billingAddress;
    }

    private function createCashpressoItems(): array
    {
        return [
            [
                'description' => 'Iphone 16',
                'reference' => 'Invoice-68775',
                'quantity' => '1',
                'unit_amount' => '100',
                'tax_amount' => '0',
            ],
        ];
    }

    private function executeCashpressoInitiate(
        string $paymentPlan,
        string $shippingMethod,
        float $amount,
        string $country = 'DE',
        ?string $shippingDate = null
    ): Transaction {
        $this->configureCashpressoService($country);
        $paymentMethod = $this->createCashpressoPaymentMethod($paymentPlan, $country);
        $customer = $this->createCashpressoCustomer();
        $billingAddress = $this->createCashpressoBillingAddress($country);
        $effectiveShippingDate = $shippingDate ?: date('Y-m-d', strtotime('+30 days'));

        try {
            return $paymentMethod->charge($amount)
                ->withCurrency('EUR')
                ->withDescription('Cashpresso APM integration test')
                ->withAddress($billingAddress, AddressType::BILLING)
                ->withAddress($billingAddress, AddressType::SHIPPING)
                ->withCustomerData($customer)
                ->withPhoneNumber('+49', '1511234567', PhoneNumberType::HOME)
                ->withCashpressoShippingMethod($shippingMethod)
                ->withShippingDate($effectiveShippingDate)
                ->withProductData($this->createCashpressoItems())
                ->execute();
        } catch (GatewayException $e) {
            if (stripos($e->getMessage(), 'App credentials not recognized') !== false) {
                $this->markTestSkipped(
                    'Cashpresso credentials are not authorized in this environment.'
                );
            }

            throw $e;
        }
    }

    private function assertCashpressoInitiateResponse(Transaction $response, string $expectedPaymentPlan): void
    {
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertContains($response->responseMessage, [TransactionStatus::INITIATED, TransactionStatus::PENDING]);
        $this->assertNotNull($response->alternativePaymentResponse);
        $this->assertNotEmpty($response->alternativePaymentResponse->redirectUrl);
        $this->assertEquals('CASHPRESSO', strtoupper((string) $response->alternativePaymentResponse->providerName));
        $this->assertEquals('BNPL', strtoupper((string) $response->alternativePaymentResponse->category));
        $this->assertEquals($expectedPaymentPlan, $response->alternativePaymentResponse->paymentPlan);
    }

    private function buildCashpressoTransactionRequest(
        ?string $paymentPlan = CashpressoPaymentPlan::PAY_30_DAYS,
        ?string $shippingMethod = CashpressoShippingMethod::DELIVERY,
        ?string $shippingDate = null,
        float $amount = 100.00,
        string $country = 'DE',
        ?array $items = null
    ): void {
        $config = $this->configureCashpressoService($country);
        $paymentMethod = $this->createCashpressoPaymentMethod(CashpressoPaymentPlan::PAY_30_DAYS, $country);
        $paymentMethod->paymentPlan = $paymentPlan;

        $customer = $this->createCashpressoCustomer();
        $billingAddress = $this->createCashpressoBillingAddress($country);

        $builder = $paymentMethod->charge($amount)
            ->withCurrency('EUR')
            ->withDescription('Cashpresso validation test')
            ->withAddress($billingAddress, AddressType::BILLING)
            ->withAddress($billingAddress, AddressType::SHIPPING)
            ->withCustomerData($customer)
            ->withPhoneNumber('+49', '1511234567', PhoneNumberType::HOME);

        if ($shippingMethod !== null) {
            $builder->withCashpressoShippingMethod($shippingMethod);
        }

        if ($shippingDate !== null) {
            $builder->withShippingDate($shippingDate);
        }

        $builder->withProductData($items ?? $this->createCashpressoItems());

        (new GpApiAuthorizationRequestBuilder())->buildRequest($builder, $config);
    }

    public function provideCashpressoShippingMethods(): array
    {
        return [
            'DELIVERY' => [CashpressoShippingMethod::DELIVERY],
            'PICKUP' => [CashpressoShippingMethod::PICKUP],
            'PICKUP_BOX' => [CashpressoShippingMethod::PICKUP_BOX],
            'POSTOFFICE' => [CashpressoShippingMethod::POSTOFFICE],
        ];
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoApiInitiatePayIn3InstallmentsMinAmountReturnsRedirectUrl(): void
    {
        $response = $this->executeCashpressoInitiate(
            CashpressoPaymentPlan::PAY_IN_3_INSTALLMENTS,
            CashpressoShippingMethod::DELIVERY,
            150.00,
            'DE'
        );

        $this->assertCashpressoInitiateResponse($response, CashpressoPaymentPlan::PAY_IN_3_INSTALLMENTS);
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoApiInitiatePay30DaysReturnsRedirectUrl(): void
    {
        $response = $this->executeCashpressoInitiate(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            100.00,
            'DE'
        );

        $this->assertCashpressoInitiateResponse($response, CashpressoPaymentPlan::PAY_30_DAYS);
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     * @dataProvider provideCashpressoShippingMethods
     */
    public function testCashpressoApiInitiateSupportsAllShippingMethods(string $shippingMethod): void
    {
        $response = $this->executeCashpressoInitiate(
            CashpressoPaymentPlan::PAY_30_DAYS,
            $shippingMethod,
            100.00,
            'DE'
        );

        $this->assertCashpressoInitiateResponse($response, CashpressoPaymentPlan::PAY_30_DAYS);
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoApiInitiateWithFutureShippingDateReturnsRedirectUrl(): void
    {
        $futureShippingDate = date('Y-m-d', strtotime('+45 days'));
        $this->assertGreaterThan(date('Y-m-d'), $futureShippingDate);

        $response = $this->executeCashpressoInitiate(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            100.00,
            'DE',
            $futureShippingDate
        );

        $this->assertCashpressoInitiateResponse($response, CashpressoPaymentPlan::PAY_30_DAYS);
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionPayIn3BelowMinimumThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('PAY_IN_3_INSTALLMENTS requires amount >= 15000 in minor units.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_IN_3_INSTALLMENTS,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d', strtotime('+30 days')),
            149.99,
            'DE'
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionMissingPaymentPlanThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('payment_method.apm.payment_plan is required for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            null,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d', strtotime('+30 days')),
            100.00,
            'DE'
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionMissingShippingMethodThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('order.shipping_method is required for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            null,
            date('Y-m-d', strtotime('+30 days')),
            100.00,
            'DE'
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionMissingShippingDateThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('order.shipping_date is required for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            null,
            100.00,
            'DE'
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionShippingDateNotFutureThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('order.shipping_date must be later than today for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d'),
            100.00,
            'DE'
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionMoreThanTenItemsThrowsArgumentException(): void
    {
        $items = [];
        for ($i = 0; $i < 11; $i++) {
            $items[] = [
                'description' => 'Item ' . $i,
                'reference' => 'REF-' . $i,
                'quantity' => '1',
                'unit_amount' => '100',
                'tax_amount' => '0',
            ];
        }

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('order.items supports a maximum of 10 entries for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d', strtotime('+30 days')),
            100.00,
            'DE',
            $items
        );
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionSupportedAtCountryBuildsRequest(): void
    {
        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d', strtotime('+30 days')),
            100.00,
            'AT'
        );

        $this->assertTrue(true);
    }

    /**
     * @group integration
     * @group apm
     * @group cashpresso
     */
    public function testCashpressoTransactionUnsupportedCountryThrowsArgumentException(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('country must be DE or AT for CASHPRESSO.');

        $this->buildCashpressoTransactionRequest(
            CashpressoPaymentPlan::PAY_30_DAYS,
            CashpressoShippingMethod::DELIVERY,
            date('Y-m-d', strtotime('+30 days')),
            100.00,
            'PL'
        );
    }

    /**
     * @group integration
     * @group hpp
     * @group cashpresso
     */
    public function testCashpressoValidHppRequestContainsCashpressoAndCreatesLink(): void
    {
        $config = $this->configureCashpressoService('DE');

        $payer = new PayerDetails();
        $payer->firstName = 'James';
        $payer->lastName = 'Mason';
        $payer->email = 'James.Mason8286@example.com';
        $payer->status = 'ACTIVE';
        $payer->id = 'PYR_992a3181a1bb493ead11474ce0fbd567';

        $billingAddress = $this->createCashpressoBillingAddress('DE');
        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = '100 main st';
        $shippingAddress->streetAddress2 = 'Guly2';
        $shippingAddress->streetAddress3 = 'Kop Strasse 1892';
        $shippingAddress->city = 'Frankfurt';
        $shippingAddress->postalCode = '60329';
        $shippingAddress->state = 'HE';
        $shippingAddress->countryCode = 'DE';

        $payerPhone = new PhoneNumber('+49', '609568831', PhoneNumberType::MOBILE);
        $shippingPhone = new PhoneNumber('+49', '609568831', PhoneNumberType::SHIPPING);

        $futureShippingDate = date('Y-m-d', strtotime('+30 days'));
        $futureExpirationDate = gmdate('Y-m-d\TH:i:s\Z', strtotime('+90 days'));

        $hppData = HPPBuilder::create()
            ->withName('Mobile Bill Payment')
            ->withDescription('February and March Invoice')
            ->withReference('TRANS-' . gmdate('YmdHis') . uniqid())
            ->withExpirationDate($futureExpirationDate)
            ->withAmount('65000')
            ->withCurrency('EUR')
            ->withPayer($payer)
            ->withPayerPhone($payerPhone)
            ->withBillingAddress($billingAddress)
            ->withShippingAddress($shippingAddress)
            ->withShippingPhone($shippingPhone)
            ->withAddressMatchIndicator(false)
            ->withNotifications(
                'https://webhook.site/62511d22-b672-41ef-afc3-03b136069aeb',
                'https://webhook.site/62511d22-b672-41ef-afc3-03b136069aeb',
                'https://webhook.site/62511d22-b672-41ef-afc3-03b136069aeb'
            )
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'DE',
                CaptureMode::AUTO,
                [HPPAllowedPaymentMethods::CARD, HPPAllowedPaymentMethods::CASHPRESSO],
                PaymentMethodUsageMode::SINGLE,
                '1'
            )
            ->withOrderReference('REF-23')
            ->withCurrencyConversionMode('NO')
            ->withApm(true, true)
            ->withCashpressoPaymentPlans([
                CashpressoPaymentPlan::PAY_IN_3_INSTALLMENTS,
                CashpressoPaymentPlan::PAY_30_DAYS,
            ])
            ->withOrderShippingMethod(CashpressoShippingMethod::DELIVERY)
            ->withOrderShippingDate($futureShippingDate)
            ->withOrderItems([
                [
                    'label' => 'Iphone 16',
                    'product_code' => 'IPH65434',
                    'quantity' => '1',
                    'unit_amount' => '65000',
                    'tax_amount' => '0',
                ],
            ])
            ->build();

        $authBuilder = HPPService::create($hppData);
        $request = (new GpApiAuthorizationRequestBuilder())->buildRequest($authBuilder, $config);
        $requestBody = $request->requestBody;

        $allowedMethods = $requestBody['order']['transaction_configuration']['allowed_payment_methods'] ?? [];
        $allowedMethods = is_array($allowedMethods) ? $allowedMethods : [$allowedMethods];
        $this->assertContains(HPPAllowedPaymentMethods::CASHPRESSO, $allowedMethods);
        $this->assertEquals('CASHPRESSO', $requestBody['order']['payment_method_configuration']['apm']['configurations'][0]['provider'] ?? null);
        $this->assertEquals(CashpressoShippingMethod::DELIVERY, $requestBody['order']['shipping_method'] ?? null);
        $this->assertEquals($futureShippingDate, $requestBody['order']['shipping_date'] ?? null);
        $this->assertGreaterThan(date('Y-m-d'), (string) ($requestBody['order']['shipping_date'] ?? ''));
        $this->assertEquals('Iphone 16', $requestBody['order']['items'][0]['label'] ?? null);
        $this->assertEquals('IPH65434', $requestBody['order']['items'][0]['product_code'] ?? null);
        $this->assertEquals('0', $requestBody['order']['tax_amount'] ?? null);

        try {
            $response = $authBuilder->execute();
        } catch (GatewayException $e) {
            if (stripos($e->getMessage(), 'App credentials not recognized') !== false) {
                $this->markTestSkipped(
                    'Cashpresso credentials are not authorized in this environment.'
                );
            }

            throw $e;
        }

        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotEmpty($response->payByLinkResponse->url);
    }
    /**
     * How to have a success running test. When you will run the test in the console it will be printed the
     * PayPal redirect url. You need to copy the link and open it in a browser, do the login wih your PayPal
     * credentials and authorize the payment in the PayPal form. You will be redirected to a blank page with a
     * printed message like this: { "success": true }. This has to be done within a 25 seconds timeframe.
     * In case you need more time update the sleep() to what you need.
     */
    public function testPayPalCharge_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->charge(1.34)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testPayPalCapture_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->authorize(1.34)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertNotEmpty($transactionSummary->transactionId);
        $this->assertNotNull($transactionSummary->transactionId);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;
        $response = $transaction->confirm()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);

        $capture = $transaction->capture()->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testPayPalFullCycle_Refund()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $trn = $this->paymentMethod->charge(1.22)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals('SUCCESS', $trn->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $trn->responseMessage);

        fwrite(STDERR, print_r($trn->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($trn->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $trnRefund = $transaction->refund()->withCurrency($this->currency)->execute();
        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);

    }

    public function testPayPalFullCycle_Reverse()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $trn = $this->paymentMethod->charge(1.22)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals('SUCCESS', $trn->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $trn->responseMessage);

        fwrite(STDERR, print_r($trn->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($trn->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);

        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $trnReverse = $transaction->reverse()->withCurrency($this->currency)->execute();

        $this->assertNotNull($trnReverse);
        $this->assertEquals('SUCCESS', $trnReverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $trnReverse->responseMessage);
    }

    public function testPayPalMultiCapture_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->authorize(3)
            ->withCurrency($this->currency)
            ->withMultiCapture(true)
            ->withDescription('PayPal Multicapture')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);

        $capture = $transaction->capture(1)->execute();
        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);

        $capture2 = $transaction->capture(2)->execute();
        $this->assertNotNull($capture2);
        $this->assertEquals('SUCCESS', $capture2->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture2->responseMessage);
    }

    /** unit_amount is actually the total amount for the item; waiting info about the shipping_discount */
    public function testPayPalChargeWithoutConfirm()
    {
        $products = [
            [
                'reference' => 'SKU251584',
                'label' => 'Magazine Subscription',
                'description' => 'Product description 1',
                'quantity' => '1',
                'unit_amount' => '7',
                'unit_currency' => $this->currency,
                'tax_amount' => '0.5'
            ],
            [
                'reference' => 'SKU8884784',
                'label' => 'Charger',
                'description' => 'Product description 2',
                'quantity' => '2',
                'unit_amount' => '6',
                'unit_currency' => $this->currency,
                'tax_amount' => '0.5'
            ]
        ];
        $order = new OrderDetails;
        $order->insuranceAmount = 10;
        $order->handlingAmount = 2;
        $order->hasInsurance = true;
        $order->description = 'Order description';

        $response = $this->paymentMethod->charge(29)
            ->withCurrency($this->currency)
            ->withDescription('New APM Uplift')
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withCustomerId('PYR_b2d3b367fcf141dcbd03cd9ccfa60519')
            ->withProductData($products)
            ->withPhoneNumber('44', '124 445 556', PhoneNumberType::WORK)
            ->withPhoneNumber('44', '124 444 333', PhoneNumberType::HOME)
            ->withPhoneNumber('1', '258 3697 144', PhoneNumberType::SHIPPING)
            ->withOrderId('124214-214221')
            ->withShippingAmount(3)
//            ->withShippingDiscount(1)
            ->withOrderDetails($order)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);
    }

    public function testAPMPendingTransaction()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::TEST_PAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->cancelUrl = 'https://example.com/cancelUrl';
        $paymentMethod->country = 'GB';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $response = $paymentMethod->charge(19.99)
            ->withCurrency('EUR')
            ->withClientTransactionId('APM-20200417')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);
        $this->assertEquals(AlternativePaymentType::TEST_PAY, $response->alternativePaymentResponse->providerName);
    }

    public function testAlipay()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $response = $paymentMethod->charge(19.99)
            ->withCurrency('HKD')
            ->withMerchantCategory(MerchantCategory::OTHER)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);
        $this->assertEquals(AlternativePaymentType::ALIPAY, $response->alternativePaymentResponse->providerName);
    }

    public function testAlipay_MissingReturnUrl()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withCurrency('HKD')
                ->withMerchantCategory(MerchantCategory::OTHER)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('returnUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAlipay_MissingStatusUrl()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withCurrency('HKD')
                ->withMerchantCategory(MerchantCategory::OTHER)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('statusUpdateUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAlipay_MissingCountry()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withCurrency('HKD')
                ->withMerchantCategory(MerchantCategory::OTHER)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('country cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAlipay_MissingAccountHolderName()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->country = 'US';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withCurrency('HKD')
                ->withMerchantCategory(MerchantCategory::OTHER)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('accountHolderName cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAlipay_MissingCurrency()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withMerchantCategory(MerchantCategory::OTHER)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('currency cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAlipay_MissingMerchantCategory()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ALIPAY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'Jane Doe';

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(19.99)
                ->withCurrency('HKD')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields merchant_category', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * eRaty redirect-url test:
     * validates that GPAPI returns INITIATED with a redirect URL.
     */
    public function testERatyRedirectUrl()
    {
        $config = $this->setUpConfig();
        $config->appId = ''; #gitleaks:allow
        $config->appKey = ''; #gitleaks:allow
        $config->country = 'PL';
        $config->accessTokenInfo->transactionProcessingAccountName = 'GPECOM_APM_Transaction_Processing';
        $config->requestLogger = new RequestConsoleLogger();
        ServicesContainer::configureService($config);

        $paymentMethod = $this->createEratyPaymentMethod();

        $customer = new Customer();
        $customer->email = 'abc@ccc.com';

        $response = $paymentMethod->charge(400)
            ->withCurrency('PLN')
            ->withCustomerId('B8J9KSQA5M6S2')
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);

        fwrite(STDERR, 'eRaty redirect URL: ' . (string) $response->alternativePaymentResponse->redirectUrl . PHP_EOL);
    }

    /**
     * Manual full-cycle eRaty flow:
     * 1) Initiate and get redirect URL
     * 2) Open URL in browser and click Pay
     * 3) Poll reporting until transaction reaches CAPTURED
     */
    public function testERatyCharge_fullCycle()
    {
        $this->configureEratyService();

        $paymentMethod = $this->createEratyPaymentMethod();

        $customer = new Customer();
        $customer->email = 'abc@ccc.com';

        $response = $paymentMethod->charge(400)
            ->withCurrency('PLN')
            ->withCustomerId('B8J9KSQA5M6S2')
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);

        $redirectUrl = (string) $response->alternativePaymentResponse->redirectUrl;
        fwrite(STDERR, "eRaty redirect URL: {$redirectUrl}" . PHP_EOL);

        // Open redirect URL in browser for manual payer authorization step.
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start "" "' . $redirectUrl . '"', 'r'));
            fwrite(STDERR, 'Browser opened. Click Pay on eRaty page to continue.' . PHP_EOL);
        } else {
            fwrite(STDERR, 'Open the redirect URL in your browser and click Pay on the eRaty page to continue.' . PHP_EOL);
        }

        // Poll reporting for up to 300 seconds to allow manual click on Pay button.
        $startDate = new DateTime();
        $summary = null;
        $deadline = time() + 300;
        while (time() < $deadline) {
            $report = ReportingService::findTransactionsPaged(1, 1)
                ->withTransactionId($response->transactionId)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::END_DATE, new DateTime())
                ->execute();

            if (!empty($report->result)) {
                /** @var TransactionSummary $candidate */
                $candidate = reset($report->result);
                if ($candidate && $candidate->transactionStatus === TransactionStatus::CAPTURED) {
                    $summary = $candidate;
                    break;
                }
            }

            sleep(5);
        }

        $this->assertNotNull($summary, 'Transaction did not reach CAPTURED. Open redirect URL and click Pay.');
        $this->assertEquals(TransactionStatus::CAPTURED, $summary->transactionStatus);
    }

    
    /**
     * eRaty report transaction detail: captured sandbox transaction.
     */
    public function testERatyReportTransactionDetailCaptured()
    {
        $this->configureEratyService();

        $response = ReportingService::transactionDetail('TRN_X3Hds5qhlvlp7we7LQVC74jVCM9Eh0_2fd52873ad53')->execute();

        $this->assertNotNull($response);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->transactionStatus);
    }

    /**
     * eRaty report transaction detail: declined sandbox transaction.
     */
    public function testERatyReportTransactionDetailDeclined()
    {
        $this->configureEratyService();

        $response = ReportingService::transactionDetail('TRN_10tXeI3vO7kQE5NGDx7GmH3y5cSaeJ_a7403b592d4f')->execute();

        $this->assertNotNull($response);
        $this->assertEquals(TransactionStatus::DECLINED, $response->transactionStatus);
    }

    /**
     * Negative eRaty validation: country is required.
     */
    public function testERatyMissingCountry()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::ERATY);
        $paymentMethod->returnUrl = 'https://example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://example.com/statusUrl';
        $paymentMethod->cancelUrl = 'https://example.com/cancelUrl';
        $paymentMethod->accountHolderName = 'John Doe';
        $paymentMethod->category = 'BNPL';
        // country is NOT set

        $exceptionCaught = false;
        try {
            $paymentMethod->charge(400)
                ->withCurrency('PLN')
                ->withCustomerId('B8J9KSQA5M6S2')
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            fwrite(STDERR, 'eRaty negative test (missing country): ' . $e->getMessage() . PHP_EOL);
            $this->assertStringContainsString('country', strtolower($e->getMessage()));
        } finally {
            $this->assertTrue($exceptionCaught, 'Expected BuilderException for missing country');
        }
    }

    /**
     * Negative eRaty validation: unsupported currency (eRaty only supports PLN).
     */
    public function testERatyUnsupportedCurrency()
    {
        $this->configureEratyService();

        $paymentMethod = $this->createEratyPaymentMethod();

        $customer = new Customer();
        $customer->email = 'abc@ccc.com';

        $exceptionCaught = false;
        try {
            // Use EUR instead of PLN - should fail
            $paymentMethod->charge(400)
                ->withCurrency('EUR')
                ->withCustomerId('B8J9KSQA5M6S2')
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            fwrite(STDERR, 'eRaty negative test (unsupported currency EUR): ' . $e->getMessage() . PHP_EOL);
            $this->assertStringContainsString('currency', strtolower($e->getMessage()));
        } finally {
            $this->assertTrue($exceptionCaught, 'Expected GatewayException for unsupported currency');
        }
    }

    /**
     * Negative eRaty validation: invalid/insufficient amount.
     */
    public function testERatyInvalidAmount()
    {
        $this->configureEratyService();

        $paymentMethod = $this->createEratyPaymentMethod();
        $customer = new Customer();
        $customer->email = 'abc@ccc.com';

        // Very low amount that eRaty should reject
        $amount = 0.01;

        $exceptionCaught = false;
        try {
            $paymentMethod->charge($amount)
                ->withCurrency('PLN')
                ->withCustomerId('B8J9KSQA5M6S2')
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            fwrite(STDERR, 'eRaty negative test (invalid amount): ' . $e->getMessage() . PHP_EOL);
            $this->assertStringContainsString('amount', strtolower($e->getMessage()));
        } finally {
            $this->assertTrue($exceptionCaught, 'Expected GatewayException for invalid amount ' . $amount);
        }
    }

    public function testBlikLevelZeroCharge()
    {
        $this->configureBlikLevelZeroService();
        $paymentMethod = $this->createBlikLevelZeroPaymentMethod();

        $customer = new Customer();
        $customer->firstName = 'James';
        $customer->lastName = 'Mason';
        $customer->email = 'james2.carl@gmail.com';

        try {
            $response = $paymentMethod->charge(10.00)
                ->withCurrency('PLN')
                ->withCustomerData($customer)
                ->withCustomerIpAddress('106.215.180.111')
                ->withCustomerUserAgent('PostmanRuntime/7.51.1')
                ->withClientTransactionId('123456789')
                ->execute();

            $this->assertNotNull($response);
            $this->assertEquals('SUCCESS', $response->responseCode);
            $this->assertNotNull($response->transactionId);
            $this->assertNotNull($response->alternativePaymentResponse);
            $this->assertEquals(
                AlternativePaymentType::BLIK,
                strtolower((string) $response->alternativePaymentResponse->providerName)
            );
        } catch (GatewayException $e) {
            if (
                str_contains($e->getMessage(), 'ACTION_NOT_AUTHORIZED')
                || str_contains($e->getMessage(), 'INVALID_TRANSACTION_ACTION')
            ) {
                $this->markTestSkipped('BLIK Level 0 QA credentials are not authorized in this environment.');
            }

            throw $e;
        }
    }

    public function testBlikLevelZeroMissingUserAgent()
    {
        $paymentMethod = $this->createBlikLevelZeroPaymentMethod();
        $errorFound = false;

        try {
            $paymentMethod->charge(10.01)
                ->withCurrency('PLN')
                ->withCustomerIpAddress('127.0.0.1')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('customerUserAgent cannot be null for BLIK Level 0 transactions.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBlikLevelZeroMissingIpAddress()
    {
        $paymentMethod = $this->createBlikLevelZeroPaymentMethod();
        $errorFound = false;

        try {
            $paymentMethod->charge(10.01)
                ->withCurrency('PLN')
                ->withCustomerUserAgent('Mozilla/5.0 Test Agent')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('customerIpAddress cannot be null for BLIK Level 0 transactions.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBlikLevelZeroPaymentCodeTooShort()
    {
        $paymentMethod = $this->createBlikLevelZeroPaymentMethod();
        $paymentMethod->paymentCode = '99999';
        $errorFound = false;

        try {
            $paymentMethod->charge(10.01)
                ->withCurrency('PLN')
                ->withCustomerIpAddress('106.215.180.111')
                ->withCustomerUserAgent('PostmanRuntime/7.51.1')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('paymentMethod->paymentCode must be exactly 6 digits for BLIK Level 0 transactions.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testBlikLevelZeroPaymentCodeTooLong()
    {
        $paymentMethod = $this->createBlikLevelZeroPaymentMethod();
        $paymentMethod->paymentCode = '9990001';
        $errorFound = false;

        try {
            $paymentMethod->charge(10.01)
                ->withCurrency('PLN')
                ->withCustomerIpAddress('106.215.180.111')
                ->withCustomerUserAgent('PostmanRuntime/7.51.1')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('paymentMethod->paymentCode must be exactly 6 digits for BLIK Level 0 transactions.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }
}