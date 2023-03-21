<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\CustomerDocument;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\BNPLShippingMethod;
use GlobalPayments\Api\Entities\Enums\BNPLType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\CustomerDocumentType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\BNPL;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class GpApiBNPLTest extends TestCase
{
    private $paymentMethod;
    private $currency;
    private $shippingAddress;
    private $billingAddress;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->paymentMethod = new BNPL(BNPLType::AFFIRM);

        $this->paymentMethod->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->cancelUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';

        $this->currency = 'USD';

        // billing address
        $this->billingAddress = new Address();
        $this->billingAddress->streetAddress1 = '10 Glenlake Pkwy NE';
        $this->billingAddress->streetAddress2 = 'no';
        $this->billingAddress->city = 'Birmingham';
        $this->billingAddress->postalCode = '50001';
        $this->billingAddress->countryCode = 'US';
        $this->billingAddress->state = 'IL';

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Apartment 852';
        $this->shippingAddress->streetAddress2 = 'Complex 741';
        $this->shippingAddress->streetAddress3 = 'no';
        $this->shippingAddress->city = 'Birmingham';
        $this->shippingAddress->postalCode = '50001';
        $this->shippingAddress->state = 'IL';
        $this->shippingAddress->countryCode = 'US';
    }

    public function setUpConfig(): GpApiConfig
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testBNPL_FullCycle()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($customer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(15);

        $trnRefund = $captureTrn->refund()->withCurrency($this->currency)->execute();
        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testFullRefund()
    {
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::TIME_CREATED)
            ->where(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::BNPL)
            ->andWith(SearchCriteria::TRANSACTION_STATUS, TransactionStatus::CAPTURED)
            ->andWith(SearchCriteria::PAYMENT_TYPE, PaymentType::SALE)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotCount(0, $response->result);
        /** @var TransactionSummary $trnSummary */
        $trnSummary = $response->result[array_rand($response->result, 1)];
        $trn = Transaction::fromId($trnSummary->transactionId, null, $trnSummary->paymentType);

        $trnRefund = $trn->refund()->withCurrency($trnSummary->currency)->execute();
        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testBNPL_PartialRefund() {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($customer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(15);

        $trnRefund = $captureTrn->refund(100)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testBNPL_MultipleRefund() {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($customer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(15);

        $trnRefund = $captureTrn->refund(100)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);

        $trnRefund = $captureTrn->refund(100)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testBNPL_Reverse()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($customer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->reverse()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $captureTrn->responseMessage);
    }

    public function testBNPL_OnlyMandatory()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);
    }

    public function testBNPL_KlarnaProvider()
    {
        $this->paymentMethod->bnplType = BNPLType::KLARNA;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);
    }

    public function testBNPL_KlarnaProvider_Refund()
    {
        $this->paymentMethod->bnplType = BNPLType::KLARNA;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(15);

        $trnRefund = $captureTrn->refund(100)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testBNPL_ClearPayProvider()
    {
        $this->paymentMethod->bnplType = BNPLType::CLEARPAY;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);
    }

    public function testBNPL_ClearPayProvider_PartialCapture()
    {
        $this->paymentMethod->bnplType = BNPLType::CLEARPAY;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture(100)->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);
    }

    public function testBNPL_ClearPayProvider_MultipleCapture()
    {
        $this->paymentMethod->bnplType = BNPLType::CLEARPAY;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->withMultiCapture(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture(100)->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(5);

        $captureTrn = $transaction->capture(100)->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);
    }

    public function testBNPL_ClearPayProvider_Refund()
    {
        $this->paymentMethod->bnplType = BNPLType::CLEARPAY;
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        fwrite(STDERR, print_r($transaction->bnplResponse->redirectUrl, TRUE));

        sleep(45);

        $captureTrn = $transaction->capture()->execute();

        $this->assertNotNull($captureTrn);
        $this->assertEquals('SUCCESS', $captureTrn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $captureTrn->responseMessage);

        sleep(15);

        $trnRefund = $captureTrn->refund(550)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);
    }

    public function testBNPL_InvalidStatusForCapture_NoRedirect()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $transaction = $this->paymentMethod->authorize(550)
            ->withCurrency($this->currency)
            ->withProductData($products)
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress, AddressType::BILLING)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($customer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);

        $exceptionCaught = false;
        try {
            $transaction->capture()
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40090', $e->responseCode);
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - id value is invalid. Please check the format and data provided is correct.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetBNPLTransactionById()
    {
        $id = 'TRN_EryDeQRtqagH27G87DkSfZGL1kiE21';

        $trnInfo = ReportingService::transactionDetail($id)->execute();

        $this->assertEquals($id, $trnInfo->transactionId);
    }

    public function testGetBNPL_RandomTransactionId()
    {
        $id = GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::transactionDetail($id)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Transactions %s not found at this /ucp/transactions/%s', $id, $id), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testGetBNPL_NullTransactionId()
    {
        $exceptionCaught = false;

        try {
            ReportingService::transactionDetail(null)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('transactionId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProducts()
    {
        $customer = $this->setCustomerData();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
                ->withCustomerData($customer)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->withOrderId('12365')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: order.items.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingShippingAddress()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
                ->withCustomerData($customer)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->withOrderId('12365')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals("Status Code: SYSTEM_ERROR - Bad Gateway",
                $e->getMessage());
            $this->assertEquals('50002', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingBillingAddress()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
                ->withCustomerData($customer)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->withOrderId('12365')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - One of the parameter is missing from the request body.",
                $e->getMessage());
            $this->assertEquals('40297', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingCustomerData()
    {
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->withOrderId('12365')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: payer.email.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingAmount()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize()
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields amount",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingCurrency()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(1)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields currency",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingCustomerEmail()
    {
        $customer = $this->setCustomerData();
        $customer->email = null;
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: payer.email.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingCustomerPhoneNumber()
    {
        $customer = $this->setCustomerData();
        $customer->phone = null;
        $products = $this->setProductList();

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: payer.contact_phone.country_code.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProductId()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();
        $products[0]->productId = null;

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: order.items[0].reference.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProductDescription()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();
        $products[0]->description = null;

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: order.items[0].description.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProductQuantity()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();
        $products[0]->quantity = null;

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals("Status Code: SYSTEM_ERROR - Bad Gateway",
                $e->getMessage());
            $this->assertEquals('50002', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProductUrl()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();
        $products[0]->url = null;

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: order.items[0].product_url.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBNPL_MissingProductImageUrl()
    {
        $customer = $this->setCustomerData();
        $products = $this->setProductList();
        $products[0]->imageUrl = null;

        $exceptionCaught = false;
        try {
            $this->paymentMethod->authorize(550)
                ->withCurrency($this->currency)
                ->withProductData($products)
                ->withAddress($this->shippingAddress, AddressType::SHIPPING)
                ->withAddress($this->billingAddress, AddressType::BILLING)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40251', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: order.items[0].product_image_url.",
                $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function setCustomerData()
    {
        $customer = new Customer();
        $customer->id = "12345678";
        $customer->firstName = 'James';
        $customer->lastName = 'Mason';
        $customer->email = 'james.mason@example.com';
        $customer->phone = new PhoneNumber('41', '57774873', PhoneNumberType::HOME);
        $customer->documents[] = new CustomerDocument('123456789', 'US', CustomerDocumentType::PASSPORT);

        return $customer;
    }

    private function setProductList()
    {
        $product = new Product();
        $product->productId = GenerationUtils::getGuid();
        $product->productName = 'iPhone 13';
        $product->description = 'iPhone 13';
        $product->quantity = 1;
        $product->unitPrice = 550;
        $product->netUnitPrice = 550;
        $product->taxAmount = 0;
        $product->discountAmount = 0;
        $product->taxPercentage = 0;
        $product->url = "https://www.example.com/iphone.html";
        $product->imageUrl = "https://www.example.com/iphone.png";

        return [$product];
    }
}