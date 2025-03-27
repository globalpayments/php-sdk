<?php

namespace GlobalPayments\Api\Test\BillPay\BillPay;

use DateTime;
use GlobalPayments\Api\Entities\{
    Address, 
    Customer,
    HostedPaymentData,
    Transaction,
};
use GlobalPayments\Api\Entities\BillPay\{Bill, BillingResponse};
use GlobalPayments\Api\Entities\Enums\{
    AccountType,
    BillPresentment,
    CheckType,
    HostedPaymentType,
    InitialPaymentMethod,
    PaymentMethodUsageMode,
    RecurringAuthorizationType,
    ScheduleFrequency,
    SecCode,
    ServiceEndpoints
};
use GlobalPayments\Api\Entities\Exceptions\{
    ApiException,
    BuilderException,
    GatewayException,
    UnsupportedTransactionException
};
use GlobalPayments\Api\Gateways\BillPayProvider;
use GlobalPayments\Api\PaymentMethods\{
    CreditCardData,
    ECheck,
    RecurringPaymentMethod
};
use GlobalPayments\Api\ServiceConfigs\Gateways\BillPayConfig;
use GlobalPayments\Api\Services\BillPayService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\{SampleRequestLogger, Logger};
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class BillPayTest extends TestCase
{
    protected ECheck $ach;
    protected CreditCardData $clearTextCredit;
    protected Address $address;
    protected Customer $customer;
    protected Bill $bill;
    /** @var Bill[] $bills */
    protected $bills = [];
    protected Bill $billLoad;
    protected Bill $blindBill;

    public function setup(): void
    {
        ServicesContainer::configureService($this->getConfig());

        $this->ach = new Echeck();
        $this->ach->accountNumber = "12345";
        $this->ach->routingNumber = "064000017";
        $this->ach->accountType = AccountType::CHECKING;
        $this->ach->checkType = CheckType::BUSINESS;
        $this->ach->secCode = SecCode::WEB;
        $this->ach->checkHolderName = "Tester";
        $this->ach->bankName = "Regions";

        $this->clearTextCredit = new CreditCardData();
        $this->clearTextCredit->number = "4444444444444448";
        $this->clearTextCredit->expMonth = "12";
        $this->clearTextCredit->expYear = "2025";
        $this->clearTextCredit->cvn = "123";
        $this->clearTextCredit->cardHolderName = "Test Tester";

        $this->address = new Address();
        $this->address->streetAddress1 = "1234 Test St";
        $this->address->streetAddress2 = "Apt 201";
        $this->address->city = "Auburn";
        $this->address->state = "AL";
        $this->address->country = "US";
        $this->address->postalCode = "12345";
        
        $this->customer = new Customer();
        $this->customer->address = $this->address;
        $this->customer->email = "testemailaddress@e-hps.com";
        $this->customer->firstName = "Test";
        $this->customer->lastName = "Tester";
        $this->customer->homePhone = "555-555-4444";

        $this->bill = new Bill();
        $this->bill->setAmount("50");
        $this->bill->setIdentifier1("12345");

        $bill1 = new Bill();
        $bill1->setBillType("Tax Payments");
        $bill1->setIdentifier1("123");
        $bill1->setAmount("10");
        $bill2 = new Bill();
        $bill2->setBillType("Tax Payments");
        $bill2->setIdentifier1("321");
        $bill2->setAmount("10");
        array_push($this->bills, $bill1, $bill2);

        $now = new DateTime('now');
        $now->modify('+3 month');

        $this->billLoad = new Bill();
        $this->billLoad->setAmount("50");
        $this->billLoad->setBillType("Tax Payments");
        $this->billLoad->setIdentifier1("12345");
        $this->billLoad->setIdentifier2("23456");
        $this->billLoad->setBillPresentment(BillPresentment::FULL);
        $this->billLoad->setDueDate($now);
        $this->billLoad->setCustomer($this->customer);

        $now = new DateTime('now');
        $now->modify('+1 month');

        $this->blindBill = new Bill();
        $this->blindBill->setAmount("50");
        $this->blindBill->setBillType("Tax Payments");
        $this->blindBill->setIdentifier1("12345");
        $this->blindBill->setIdentifier2("23456");
        $this->blindBill->setBillPresentment(BillPresentment::FULL);
        $this->blindBill->setDueDate($now);
        $this->blindBill->setCustomer($this->customer);
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new BillPayConfig();
        $config->setMerchantName("Dev_Exp_Team_Merchant");
        $config->setUsername("DevExpTeam");
        //$config->setUsername("dev_exp_fh");
        $config->setPassword("devexpteam_R0cks!");
        $config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION;
        // Uncomment if you want to implement logger
        $config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
        return $config;
    }

    public function testChargeWithSingleBillReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        $result = $this->clearTextCredit->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();

        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeWithMultipleBillsReturnSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        $totalAmount = 0.0;

        foreach($this->bills as $bill) {
            $totalAmount = $totalAmount + $bill->getAmount();
        }

        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $totalAmount);

        $result = $this->clearTextCredit->charge($totalAmount)
            ->withAddress($this->address)
            ->withBills($this->bills)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();

        $this->validateSuccessfulTransaction($result);
    }

    public function testTokenizeUsingCreditCardReturnsToken() : void
    {
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->clearTextCredit->verify()
                ->withAddress($address)
                ->withRequestMultiUseToken(true)
                ->execute();
                
        $this->assertFalse(empty($response->token));
    }

    public function testTokenExpiryUsingCreditCardTokenDoesNotThrow() : void
    {
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->clearTextCredit->verify()
                ->withAddress($address)
                ->withRequestMultiUseToken(true)
                ->execute();
        
        $this->assertFalse(empty($response->token));

        try {
            $this->clearTextCredit->token = $response->token;
            $this->clearTextCredit->expMonth = "12";
            $this->clearTextCredit->expYear = "2025";

            $this->clearTextCredit->updateTokenExpiry();
        } catch (ApiException $e) {
            $this->expectExceptionMessage($e->getMessage());
        }
    }

    public function testTokenizeUsingACHReturnsToken() : void
    {
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->ach->verify()
                ->withAddress($address)
                ->withRequestMultiUseToken(true)
                ->execute();
                
        $this->assertFalse(empty($response->token));
    }

    public function testChargeUsingTokenizedCreditCardReturnsSuccessfulTransaction() : void
    {
        $service = new BillPayService();
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->clearTextCredit->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->execute();

        $token = $response->token;
        
        $this->assertFalse(empty($token));

        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        $paymentMethod = new CreditCardData();
        $paymentMethod->token = $token;
        $paymentMethod->expMonth = $this->clearTextCredit->expMonth;
        $paymentMethod->expYear = $this->clearTextCredit->expYear;

        $this->assertFalse(empty($token));

        $response = $paymentMethod->charge($this->bill->getAmount())
            ->withAddress($address)
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency('USD')
            ->execute();

        $this->validateSuccessfulTransaction($response);
    }

    public function testTokenizeUsingCreditCardReturnsTokenDetailsAlongWithCardBrand(): void
    {
        $cardTypeVisa = "VISA";
        $cardTypeDiscover = "DISC";
        $cardTypeMasterCard = "MC";
        $cardTypeAmericanExpress = "AMEX";

        $postalCode = $this->address->postalCode;
        $custFirstName = $this->customer->firstName;
        $custLastName = $this->customer->lastName;
        $cardHolderName = "Test Tester";

        $now = new DateTime('now');

        $clearTextCreditVisa = new CreditCardData();
        $clearTextCreditVisa->number = "4444444444444448";
        $clearTextCreditVisa->expMonth = $now->format('n');
        $clearTextCreditVisa->expYear = $now->format('Y');
        $clearTextCreditVisa->cvn = "123";
        $clearTextCreditVisa->cardHolderName = $cardHolderName;

        $clearTextCreditDiscover = new CreditCardData();
        $clearTextCreditDiscover->number = "6011000000000087";
        $clearTextCreditDiscover->expMonth = $now->format('n');
        $clearTextCreditDiscover->expYear = $now->format('Y');
        $clearTextCreditDiscover->cvn = "123";
        $clearTextCreditDiscover->cardHolderName = $cardHolderName;

        $clearTextCreditMasterCard = new CreditCardData();
        $clearTextCreditMasterCard->number = "5425230000004415";
        $clearTextCreditMasterCard->expMonth = $now->format('n');
        $clearTextCreditMasterCard->expYear = $now->format('Y');
        $clearTextCreditMasterCard->cvn = "123";
        $clearTextCreditMasterCard->cardHolderName = $cardHolderName;

        $clearTextCreditAmericanExpress = new CreditCardData();
        $clearTextCreditAmericanExpress->number = "374101000000608";
        $clearTextCreditAmericanExpress->expMonth = $now->format('n');
        $clearTextCreditAmericanExpress->expYear = $now->format('Y');
        $clearTextCreditAmericanExpress->cvn = "123";
        $clearTextCreditAmericanExpress->cardHolderName = $cardHolderName;

        // VISA
        $tokenResponseVisa = $clearTextCreditVisa->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withBill($this->bill)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($tokenResponseVisa);

        $clearTextCreditVisa->token = $tokenResponseVisa->token;
        $tokenInfoResponseVisa = $clearTextCreditVisa->getTokenInformation();

        $this->assertNotNull($tokenInfoResponseVisa);
        $this->assertEquals($cardTypeVisa, $tokenInfoResponseVisa->cardType);
        $this->assertEquals($cardTypeVisa, $tokenInfoResponseVisa->cardDetails->brand);
        $this->assertNotNull($tokenInfoResponseVisa->address);

        $this->assertEquals($postalCode, $tokenInfoResponseVisa->address->postalCode);
        $this->assertEquals($custFirstName, $tokenInfoResponseVisa->customerData->firstName);
        $this->assertEquals($custLastName, $tokenInfoResponseVisa->customerData->lastName);

        $this->assertNotNull($tokenInfoResponseVisa->tokenData);
        $this->assertNotNull($tokenInfoResponseVisa->tokenData->getLastUsedDateUTC());
        $this->assertTrue($tokenInfoResponseVisa->tokenData->getLastUsedDateUTC()instanceof DateTime);
        $this->assertFalse(count($tokenInfoResponseVisa->tokenData->getMerchants()) === 0);
        $this->assertEquals("Dev_Exp_Team_Merchant", $tokenInfoResponseVisa->tokenData->getMerchants()[0]);
        
        // Discover
        $tokenResponseDiscover = $clearTextCreditDiscover->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withBill($this->bill)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();
        
        $this->assertNotNull($tokenResponseDiscover);
        
        $clearTextCreditDiscover->token = $tokenResponseDiscover->token;
        $tokenInfoResponseDiscover = $clearTextCreditDiscover->getTokenInformation();

        $this->assertNotNull($tokenResponseDiscover);
        $this->assertEquals($cardTypeDiscover, $tokenInfoResponseDiscover->cardType);
        $this->assertEquals($cardTypeDiscover, $tokenInfoResponseDiscover->cardDetails->brand);
        $this->assertNotNull($tokenInfoResponseDiscover->address);

        $this->assertEquals($postalCode, $tokenInfoResponseDiscover->address->postalCode);
        $this->assertEquals($custFirstName, $tokenInfoResponseDiscover->customerData->firstName);
        $this->assertEquals($custLastName, $tokenInfoResponseDiscover->customerData->lastName);

        $this->assertNotNull($tokenInfoResponseDiscover->tokenData);
        $this->assertNotNull($tokenInfoResponseDiscover->tokenData->getLastUsedDateUTC());
        $this->assertTrue($tokenInfoResponseDiscover->tokenData->getLastUsedDateUTC()instanceof DateTime);
        $this->assertFalse(count($tokenInfoResponseDiscover->tokenData->getMerchants()) === 0);
        $this->assertEquals("Dev_Exp_Team_Merchant", $tokenInfoResponseDiscover->tokenData->getMerchants()[0]);

        // Master Card
        $tokenResponseMasterCard = $clearTextCreditMasterCard->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withBill($this->bill)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($tokenResponseMasterCard);

        $clearTextCreditMasterCard->token = $tokenResponseMasterCard->token;
        $tokenResponseMasterCard = $clearTextCreditMasterCard->getTokenInformation();

        $this->assertNotNull($tokenResponseMasterCard);
        $this->assertEquals($cardTypeMasterCard, $tokenResponseMasterCard->cardType);
        $this->assertEquals($cardTypeMasterCard, $tokenResponseMasterCard->cardDetails->brand);

        $this->assertNotNull($tokenResponseMasterCard->address);
        $this->assertEquals($postalCode, $tokenResponseMasterCard->address->postalCode);
        $this->assertEquals($custFirstName, $tokenResponseMasterCard->customerData->firstName);
        $this->assertEquals($custLastName, $tokenResponseMasterCard->customerData->lastName);

        $this->assertNotNull($tokenResponseMasterCard->tokenData);
        $this->assertNotNull($tokenResponseMasterCard->tokenData->getLastUsedDateUTC());
        $this->assertTrue($tokenResponseMasterCard->tokenData->getLastUsedDateUTC()instanceof DateTime);
        $this->assertFalse(count($tokenResponseMasterCard->tokenData->getMerchants()) === 0);
        $this->assertEquals("Dev_Exp_Team_Merchant", $tokenResponseMasterCard->tokenData->getMerchants()[0]);

        // American Express
        $tokenResponseAmericanExpress = $clearTextCreditAmericanExpress->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withBill($this->bill)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();
        
        $this->assertNotNull($tokenResponseAmericanExpress);

        $clearTextCreditAmericanExpress->token = $tokenResponseAmericanExpress->token;
        $tokenResponseAmericanExpress = $clearTextCreditAmericanExpress->getTokenInformation();

        $this->assertNotNull($tokenResponseAmericanExpress);
        $this->assertEquals($cardTypeAmericanExpress, $tokenResponseAmericanExpress->cardType);
        $this->assertEquals($cardTypeAmericanExpress, $tokenResponseAmericanExpress->cardDetails->brand);

        $this->assertNotNull($tokenResponseAmericanExpress->address);
        $this->assertEquals($postalCode, $tokenResponseAmericanExpress->address->postalCode);
        $this->assertEquals($custFirstName, $tokenResponseAmericanExpress->customerData->firstName);
        $this->assertEquals($custLastName, $tokenResponseAmericanExpress->customerData->lastName);

        $this->assertNotNull($tokenResponseAmericanExpress->tokenData);
        $this->assertNotNull($tokenResponseAmericanExpress->tokenData->getLastUsedDateUTC());
        $this->assertTrue($tokenResponseAmericanExpress->tokenData->getLastUsedDateUTC()instanceof DateTime);
        $this->assertFalse(count($tokenResponseAmericanExpress->tokenData->getMerchants()) === 0);
        $this->assertEquals("Dev_Exp_Team_Merchant", $tokenResponseAmericanExpress->tokenData->getMerchants()[0]);
    }

    public function testChargeUsingTokenizedACHReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->ach->verify()
                ->withAddress($address)
                ->withRequestMultiUseToken(true)
                ->execute();

        $token = $response->token;
        assertFalse(empty($token));
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        $paymentMethod = new ECheck();
        $paymentMethod->accountType = AccountType::CHECKING;
        $paymentMethod->checkType = CheckType::BUSINESS;
        $paymentMethod->secCode = SecCode::WEB;
        $paymentMethod->checkHolderName = "Tester";
        $paymentMethod->token = $token;

        assertFalse(empty($token));

        $result = $paymentMethod->charge($this->bill->getAmount())
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->withAddress($address)
            ->execute();
        
        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeUsingTokenFromPreviousPaymentReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        $transaction = $this->clearTextCredit->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->validateSuccessfulTransaction($transaction);
        assertFalse(empty($transaction->token));

        $token = new CreditCardData();
        $token->token = $transaction->token;
        $token->expYear = $this->clearTextCredit->expYear;
        $token->expMonth = $this->clearTextCredit->expMonth;

        $result = $token->charge($this->bill->getAmount())
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();
    }

    public function testChargeWithoutAddingBillsThrowsBuilderException(): void 
    {
        try {
            $result = $this->clearTextCredit->charge(50.0)
                ->withAddress($this->address)
                ->withConvenienceAmount(3.0)
                ->withCurrency("USD")
                ->execute();

                $this->fail('Success Transaction');
        } catch(BuilderException $e) {
            $this->assertTrue($e instanceof BuilderException);
        }
    }

    public function testChargeWithMismatchingAmountsThrowsBuilderException(): void
    {
        try {
            $result = $this->clearTextCredit
                ->charge(60.0)
                ->withBills($this->bills)
                ->withCurrency("USD")
                ->execute();

            $this->fail('Successful Transaction');
        } catch (BuilderException $e) {
            assertTrue($e instanceof BuilderException);
        }
    }

    public function testReversePaymentWithPreviousTransactionReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        // Make transaction to reverse
        $transaction = $this->clearTextCredit->charge($this->bill->getAmount())
        ->withAddress($this->address)
        ->withBill($this->bill)
        ->withConvenienceAmount($fee)
        ->withCurrency("USD")
        ->execute();

        $this->validateSuccessfulTransaction($transaction);

        // Now reverse it
        $reversal = new Transaction();
        $reversal->transactionId = $transaction->transactionId;
        $reversal->reverse($this->bill->getAmount())
            ->withConvenienceAmount($fee)
            ->execute();

        $this->validateSuccessfulTransaction($reversal);
    }

    public function testReversePaymentWithPreviousMultiBillTransactionReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        /** @var float */
        $totalAmount = 0;

        foreach ($this->bills as $bill) {
            $totalAmount = $totalAmount + $bill->getAmount();
        }

        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $totalAmount);

        // Make transaction to reverse
        $transaction = $this->clearTextCredit->charge($totalAmount)
            ->withAddress($this->address)
            ->withBills($this->bills)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();

        $this->validateSuccessfulTransaction($transaction);

        // Now reverse it
        $reversal = new Transaction();
        $reversal->transactionId = $transaction->transactionId;
        $reversal->reverse($totalAmount)
            ->withConvenienceAmount($fee)
            ->execute();

        $this->validateSuccessfulTransaction($reversal);
    }

    public function testPartialReversalWithCreditCardReturnsSuccessfulTransaction(): void
    {
        $service = new BillPayService();
        /** @var float */
        $totalAmount = 0;

        foreach ($this->bills as $bill) {
            $totalAmount = $totalAmount + $bill->getAmount();
        }

        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $totalAmount);

        // Make transaction to reverse
        $transaction = $this->clearTextCredit->charge($totalAmount)
            ->withAddress($this->address)
            ->withBills($this->bills)
            ->withPaymentMethod($this->clearTextCredit)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();

        $this->validateSuccessfulTransaction($transaction);

        // Now reverse it

        /** var array<Bill> */
        $billsToPartiallyReverse = [];
        foreach ($this->bills as $x)
        {
            $bill = new Bill();
            $bill->setBillType($x->getBillType());
            $bill->setIdentifier1($x->getIdentifier1());
            $bill->setAmount($x->getAmount() - 5);

            array_push($billsToPartiallyReverse, $bill);
        }

        $newTotalAmount = $totalAmount - 10.0;
        $newFees = $service->calculateConvenienceAmount($this->clearTextCredit, $newTotalAmount);

        $reversal = new Transaction();
        $reversal->transactionId = $transaction->transactionId;
        $reversal->reverse($newTotalAmount)
            ->withBills($billsToPartiallyReverse)
            ->withConvenienceAmount($fee - $newFees)
            ->execute();

        $this->validateSuccessfulTransaction($reversal);
    }

    public function testLoadHostedPaymentWithMakePaymentTypeReturnsIdentifier(): void
    {
        $service = new BillPayService();
        $hostedPaymentData = new HostedPaymentData();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->blindBill);

        $address = new Address();
        $address->streetAddress1 = "123 Drive";
        $address->city = "Auburn";
        $address->state = "AL";
        $address->postalCode = "36830";
        $address->country = "US";


        $hostedPaymentData->bills = $bills;
        $hostedPaymentData->customerAddress = $address;
        $hostedPaymentData->customerEmail = "test@tester.com";
        $hostedPaymentData->customerFirstName = "Test";
        $hostedPaymentData->customerLastName = "Tester";
        $hostedPaymentData->customerPhoneMobile = "800-555-5555";
        $hostedPaymentData->customerIsEditable = true;
        $hostedPaymentData->hostedPaymentType = HostedPaymentType::MAKE_PAYMENT_RETURN_TOKEN;

        $response = $service->loadHostedPayment($hostedPaymentData);

        $this->assertTrue(!empty($response->getPaymentIdentifier()));
    }

    public function testLoadHostedPaymentWithMakePaymentReturnTokenReturnsIdentifier(): void
    {
        $service = new BillPayService();
        $hostedPaymentData = new HostedPaymentData();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->blindBill);

        $address = new Address();
        $address->streetAddress1 = "1234 Drive";
        $address->city = "Auburn";
        $address->state = "AL";
        $address->postalCode = "36830";
        $address->country = "US";

        $hostedPaymentData->bills = $bills;
        $hostedPaymentData->customerAddress = $address;
        $hostedPaymentData->customerEmail = "test@tester.com";
        $hostedPaymentData->customerFirstName = "Test";
        $hostedPaymentData->customerLastName = "Tester";
        $hostedPaymentData->customerPhoneMobile = "800-555-5555";
        $hostedPaymentData->customerIsEditable = true;
        $hostedPaymentData->hostedPaymentType = HostedPaymentType::MAKE_PAYMENT_RETURN_TOKEN;

        $response = $service->loadHostedPayment($hostedPaymentData);

        $this->assertTrue(!empty($response->getPaymentIdentifier()));
    }

    public function testLoadHostedPaymentWithoutBillsThrowsBuilderException(): void
    {
        try {
            $service = new BillPayService();
            $hostedPaymentData = new HostedPaymentData();

            $address = new Address();
            $address->streetAddress1 = "1234 Drive";

            $hostedPaymentData->customerAddress = $address;
            $hostedPaymentData->customerEmail = "alexander.molbert@e-hps.com";
            $hostedPaymentData->customerFirstName = "Alex";
            $hostedPaymentData->hostedPaymentType = HostedPaymentType::MAKE_PAYMENT;

            $response = $service->loadHostedPayment($hostedPaymentData);

            $this->fail('Successful Transaction');
        } catch (BuilderException $e) {
            assertTrue($e instanceof BuilderException);
        } 
    }

    public function testLoadHostedPaymentWithoutPaymentTypeThrowsBuilderException(): void
    {
        try {
            $service = new BillPayService();
            $hostedPaymentData = new HostedPaymentData();

            /** @var array<Bill> */
            $bills = [];
            array_push($bills, $this->blindBill);

            $address = new Address();
            $address->streetAddress1 = "1234 Drive";
        
            $hostedPaymentData->bills = $bills;
            $hostedPaymentData->customerAddress = $address;
            $hostedPaymentData->customerEmail = "alexander.molbert@e-hps.com";
            $hostedPaymentData->customerFirstName = "Alex";

            $response = $service->loadHostedPayment($hostedPaymentData);

            $this->fail('Successful Transaction');
        } catch (BuilderException $e) {
            assertTrue($e instanceof BuilderException);
        } 
    }

    public function testLoadWithOneBillDoesNotThrow(): void
    {
        try {
            $service = new BillPayService();

            /** @var array<Bill> */
            $bills = [];
            array_push($bills, $this->blindBill);

            $service->loadBills($bills);

            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testLoadWithOneThousandBillsDoesNotThrow(): void
    {
        try {
            $service = new BillPayService();

            $service->loadBills($this->makeNumberOfBills(1000));

            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testLoadWithFiveThousandBillsDoesNotThrow(): void
    {
        try {
            $service = new BillPayService();

            $service->loadBills($this->makeNumberOfBills(5000));

            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testLoadWithDuplicateBillsThrowsGatewayException(): void
    {
        $service = new BillPayService();

        /** @var array<Bill> */
        $bills = [];

        array_push($bills, $this->billLoad);
        array_push($bills, $this->billLoad);

        $this->expectException(GatewayException::class);
        $service->loadBills($bills);
    }

    public function testLoadWithInvalidBillTypeThrowsGatewayException(): void
    {
        try {
            $service = new BillPayService();
            /** @var array<Bill> */
            $bills = [];
            array_push($bills, $this->billLoad);

            $newBill = new Bill();
            $newBill->setAmount($this->billLoad->getAmount());
            $newBill->setBillPresentment($this->billLoad->getBillPresentment());
            $newBill->setBillType("InvalidBillType");
            $newBill->setCustomer($this->billLoad->getCustomer());
            $newBill->setDueDate($this->billLoad->getDueDate());
            $newBill->setIdentifier1($this->billLoad->getIdentifier1());
            array_push($bills, $newBill);

            $service->loadBills($bills);

            $this->fail('Successful Transaction');
        } catch (GatewayException $e){
            assertTrue($e instanceof GatewayException);
        }
    }

    public function testCreateCustomerReturnsCustomer(): void
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "IntegrationCreate";
            $this->customer->lastName = "Customer";
            $this->customer->email = "test.test@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            $this->assertEquals("IntegrationCreate", $this->customer->firstName);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testUpdateCustomerReturnsCustomer(): void
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "IntegrationUpdate";
            $this->customer->lastName = "Customer";
            $this->customer->email = "test.test@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            $this->assertEquals("IntegrationUpdate", $this->customer->firstName);

            $this->customer->firstName = "Updated";

            $this->customer->saveChanges();

            $this->assertEquals("Updated", $this->customer->firstName);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testDeleteCustomerReturnsCustomer(): void
    {
        $id = uniqid();

        try {
            $this->customer = new Customer();
            $this->customer->firstName = "IntegrationDelete";
            $this->customer->lastName = "Customer";
            $this->customer->email = "test.test@test.com";
            $this->customer->id = $id;
            $this->customer->create();

            $this->assertEquals("IntegrationDelete", $this->customer->firstName);

            $this->customer->delete();
            
            // Bill pay currently does not support retrieval of customer, so there is no true
            // way to validate the customer was deleted other than no exception was thrown
            $this->assertEquals("IntegrationDelete", $this->customer->firstName);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCreateCustomerAccountReturnsPaymentMethod(): void
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "Integration";
            $this->customer->lastName = "Customer";
            $this->customer->email = "test.test@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $this->customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
           
            $this->assertFalse($paymentMethod->key === "");
            $this->assertFalse($paymentMethod->key === null);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }   
    }

    public function testUpdateCustomerAccountReturnsSuccess()
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "Account";
            $this->customer->lastName = "Update";
            $this->customer->email = "account.update@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $this->customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
           
            $this->assertFalse($paymentMethod->key === "");
            $this->assertFalse($paymentMethod->key === null);

            /** @var CreditCardData */
            $creditCardData = $paymentMethod->paymentMethod;
            $creditCardData->expYear = 2026;

            $paymentMethod->saveChanges();
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }   
    }

    public function testDeleteCustomerAccountReturnsSuccess()
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "Account";
            $this->customer->lastName = "Delete";
            $this->customer->email = "account.delete@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $this->customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
           
            $this->assertFalse($paymentMethod->key === "");
            $this->assertFalse($paymentMethod->key === null);

            $paymentMethod->delete();
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }   
    }

    public function testDeleteNonexistingCustomerThrowsApiException() 
    {
        $customer = new Customer();
        $customer->firstName = "Incog";
        $customer->lastName = "Anony";
        $customer->id = "DoesntExist";

        $this->expectException(ApiException::class);
        $customer->delete();
    }

    public function testCreateRecurringPaymentMonthlyPositive()
    {
        try {
            $this->customer = new Customer();
            $this->customer->firstName = "Account";
            $this->customer->lastName = "Update";
            $this->customer->email = "account.update@test.com";
            $this->customer->id = uniqid();
            $this->customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $this->customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
            
            $this->customer->address = $this->address;

            /** Schedule */
            $recur = $paymentMethod->addSchedule(uniqid())
                ->withAmount(50.0)
                ->withBill($this->blindBill)
                ->withCustomer($this->customer)
                ->withStartDate(new \DateTime())
                ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2026-12-21'))
                ->withnumberOfPaymentsRemaining(27)
                ->withFrequency(ScheduleFrequency::MONTHLY)
                ->withToken($paymentMethod->token)
                ->withPrimaryConvenienceAmount(5.0)
                ->withLastPrimaryConvenienceAmount(4.0)
                ->withRecurringAuthorizationType(RecurringAuthorizationType::UNASSIGNED)
                ->withInitialPaymentMethod(InitialPaymentMethod::CARD)
                ->create();
            
            $this->assertNotNull($recur);
            $this->assertFalse($paymentMethod->token === "");
            $this->assertFalse($paymentMethod->token === null);
        } catch (ApiException $e) {
            $this->fail('Error test testCreateRecurringPaymentMonthlyPositive');
        }
    }

    public function testCreateRecurringScheduleSemiMonthlySecondInstanceDateRequiredNegative()
    {
        $this->customer = new Customer();
        $this->customer->firstName = "Account";
        $this->customer->lastName = "Update";
        $this->customer->email = "account.update@test.com";
        $this->customer->id = uniqid();
        $this->customer->create();

        /** @var RecurringPaymentMethod */
        $paymentMethod = $this->customer
            ->addPaymentMethod(uniqid(), $this->clearTextCredit)
            ->create();

        $this->customer->address = $this->address;
        $this->bill->setBillType("Tax Payments");

        try {
            $recur = $paymentMethod->addSchedule(uniqid())
                ->withAmount(50.0)
                ->withBill($this->bill)
                ->withCustomer($this->customer)
                ->withStartDate(new \DateTime())
                ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2026-12-21'))
                ->withnumberOfPaymentsRemaining(27)
                ->withFrequency(ScheduleFrequency::SEMI_MONTHLY)
                ->withToken($paymentMethod->token)
                ->withPrimaryConvenienceAmount(5.0)
                ->withLastPrimaryConvenienceAmount(4.0)
                ->withRecurringAuthorizationType(RecurringAuthorizationType::UNASSIGNED)
                ->withInitialPaymentMethod(InitialPaymentMethod::CARD)
                ->create();

                $this->fail("Transaction should call UnsupportedTransactionException");
        } catch (ApiException $e) {
            $this->assertTrue($e->getMessage() === 'Second Instance Date is required for the semi-monthly schedule.');
        }
    }

    public function testCreateRecurringScheduleMonthlyPrimaryAccountTokenRequiredNegative()
    {
        $this->customer = new Customer();
        $this->customer->firstName = "Account";
        $this->customer->lastName = "Update";
        $this->customer->email = "account.update@test.com";
        $this->customer->id = uniqid();
        $this->customer->create();

        /** @var RecurringPaymentMethod */
        $paymentMethod = $this->customer
            ->addPaymentMethod(uniqid(), $this->clearTextCredit)
            ->create();

        $this->customer->address = $this->address;
        $this->bill->setBillType("Tax Payments");

        try {
            $recur = $paymentMethod->addSchedule(uniqid())
                ->withAmount(50.0)
                ->withBill($this->bill)
                ->withCustomer($this->customer)
                ->withStartDate(new \DateTime())
                ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2026-12-21'))
                ->withnumberOfPaymentsRemaining(27)
                ->withFrequency(ScheduleFrequency::MONTHLY)
                ->withPrimaryConvenienceAmount(5.0)
                ->withLastPrimaryConvenienceAmount(4.0)
                ->withRecurringAuthorizationType(RecurringAuthorizationType::UNASSIGNED)
                ->withInitialPaymentMethod(InitialPaymentMethod::CARD)
                ->create();

                $this->fail("Transaction should call UnsupportedTransactionException");
        } catch (ApiException $e) {
            $this->assertTrue($e->getMessage() === 'Primary token is required to perform recurring transaction.');
        }

    }

    public function testCreateRecurringScheduleMonthlyScheduleTypeRequiredNegative()
    {
        $this->customer = new Customer();
        $this->customer->firstName = "Account";
        $this->customer->lastName = "Update";
        $this->customer->email = "account.update@test.com";
        $this->customer->id = uniqid();
        $this->customer->create();

        /** @var RecurringPaymentMethod */
        $paymentMethod = $this->customer
            ->addPaymentMethod(uniqid(), $this->clearTextCredit)
            ->create();

        $this->customer->address = $this->address;
        $this->bill->setBillType("Tax Payments");

        try {
            $recur = $paymentMethod->addSchedule(uniqid())
                ->withAmount(50.0)
                ->withBill($this->blindBill)
                ->withCustomer($this->customer)
                ->withStartDate(new \DateTime())
                ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2026-12-21'))
                ->withnumberOfPaymentsRemaining(27)
                ->withToken($paymentMethod->token)
                ->withPrimaryConvenienceAmount(5.0)
                ->withLastPrimaryConvenienceAmount(4.0)
                ->withRecurringAuthorizationType(RecurringAuthorizationType::UNASSIGNED)
                ->withInitialPaymentMethod(InitialPaymentMethod::CARD)
                ->create();

            $this->fail("Transaction should call UnsupportedTransactionException");
        } catch (ApiException $e) {
            $this->assertTrue($e->getMessage() === 'Schedule Type is required to perform recurring transaction.');
        }
    }

    public function testChargeMakeQuickPayBlindPaymentACH()
    {
        $this->getQuickPayConfig();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $ach = new ECheck();
        $ach->accountNumber = "987987987";
        $ach->routingNumber = "062000080";
        $ach->accountType = AccountType::CHECKING;
        $ach->checkType = CheckType::PERSONAL;
        $ach->secCode = SecCode::WEB;
        $ach->checkHolderName = "Hank Hill";
        $ach->bankName = "Regions";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/'
        $ach->token = "8FA0ACE8-7CAC-4169-A10D-874DA6AC0923";

        /** @var Transaction */
        $result = $ach->charge($bill->getAmount())
            ->withAddress($address)
            ->withCustomerData($customer)
            ->withBill($bill)
            ->withConvenienceAmount(2.65)
            ->withCurrency("USD")
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->execute();

        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeMakeQuickPayBlindPaymentCredit()
    {
        $this->getQuickPayConfig();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $cardData = new CreditCardData();
        $cardData->number = "5454545454545454";
        $cardData->expMonth = "12";
        $cardData->expYear = "2025";
        $cardData->cvn = "123";
        $cardData->cardHolderName = "Hank Hill";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/'
        $cardData->token = "286694D3-5FE3-46D7-B915-F7C9EA76DC2F";

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($cardData, $bill->getAmount());

        /** @var Transaction */
        $result = $cardData->charge($bill->getAmount())
            ->withAddress($address)
            ->withBill($bill)
            ->withCustomerData($customer)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->execute();

        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeMakeQuickPayBlindPaymentReturnTokenCredit(): void
    {
        $this->getQuickPayConfig();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $cardData = new CreditCardData();
        $cardData->number = "5454545454545454";
        $cardData->expMonth = "12";
        $cardData->expYear = "2025";
        $cardData->cvn = "123";
        $cardData->cardHolderName = "Hank Hill";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/'
        $cardData->token = "9109B0ED-A98C-494B-8AFA-C8BE9DC25752";

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($cardData, $bill->getAmount());

        /** @var Transaction */
        $result = $cardData->charge($bill->getAmount())
            ->withCurrency("USD")
            ->withAddress($address)
            ->withCustomerData($customer)
            ->withBill($bill)
            ->withConvenienceAmount($fee)
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->execute();
        
        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeMakeQuickPayBlindPaymentReturnTokenACH()
    {
        $this->getQuickPayConfig();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $ach = new ECheck();
        $ach->accountNumber = "987987987";
        $ach->routingNumber = "062000080";
        $ach->accountType = AccountType::CHECKING;
        $ach->checkType = CheckType::PERSONAL;
        $ach->secCode = SecCode::WEB;
        $ach->checkHolderName = "Hank Hill";
        $ach->bankName = "Regions";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/'
        $ach->token = "2BC7D494-E803-4296-81A5-72C565D33F09";

        /** @var Transaction */
        $result = $ach->charge($bill->getAmount())
            ->withCurrency("USD")
            ->withAddress($address)
            ->withBill($bill)
            ->withConvenienceAmount(2.65)
            ->withCustomerData($customer)
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->execute();
        
        $this->validateSuccessfulTransaction($result);
    }

    public function testChargeMakeQuickPayBlindPaymentACHTokenNotPassedExceptionCase()
    {
        $this->getQuickPayConfig();

        try {
            $this->ach->charge($this->bill->getAmount())
                ->withAddress($this->address)
                ->withCustomerData($this->customer)
                ->withBill($this->bill)
                ->withConvenienceAmount(2.65)
                ->withCurrency("USD")
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();

            $this->fail("Transaction should call UnsupportedTransactionException");
        } catch (UnsupportedTransactionException $e) {
            $this->assertEquals(
                "Quick Pay token must be provided for this transaction",
                $e->getMessage()
            );
        }
    }

    public function testChargeMakeQuickPayBlindPaymentCreditTokenNotPassedExceptionCase()
    {
        $this->getQuickPayConfig();

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        try {
            $this->clearTextCredit->charge($this->bill->getAmount())
                ->withAddress($this->address)
                ->withBill($this->bill)
                ->withCustomerData($this->customer)
                ->withConvenienceAmount($fee)
                ->withCurrency("USD")
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();

            $this->fail("Transaction should call UnsupportedTransactionException");

        } catch (UnsupportedTransactionException $e) {
            $this->assertEquals(
                "Quick Pay token must be provided for this transaction",
                $e->getMessage()
            );
        }
    }

    public function testChargeMakeQuickPayBlindPaymentReturnTokenCreditTokenNotPassedExceptionCase()
    {
        $this->getQuickPayConfig();

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        try {
            $this->clearTextCredit->charge($this->bill->getAmount())
                ->withCurrency("USD")
                ->withAddress($this->address)
                ->withCustomerData($this->customer)
                ->withBill($this->bill)
                ->withConvenienceAmount($fee)
                ->withRequestMultiUseToken(true)       
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();

            $this->fail("Transaction should call UnsupportedTransactionException");

        } catch (UnsupportedTransactionException $e) {
            $this->assertEquals(
                "Quick Pay token must be provided for this transaction",
                $e->getMessage()
            );
        }
    }

    public function testChargeMakeQuickPayBlindPaymentReturnTokenACHTokenNotPassedExceptionCase()
    {
        $this->getQuickPayConfig();

        try {
            $this->ach->charge($this->bill->getAmount())
                ->withCurrency("USD") 
                ->withAddress($this->address)
                ->withCustomerData($this->customer)
                ->withBill($this->bill)
                ->withConvenienceAmount(2.65)
                ->withRequestMultiUseToken(true)
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();

            $this->fail("Transaction should call UnsupportedTransactionException");
        } catch (UnsupportedTransactionException $e) {
            $this->assertEquals(
                "Quick Pay token must be provided for this transaction",
                $e->getMessage()
            );
        }
    }

    public function testACHAccountToCharge()
    {
        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount($this->clearTextCredit, $this->bill->getAmount());

        $billpayProvider = new BillPayProvider();

        /** @var Transaction */
        $result = $this->ach->charge($this->bill->getAmount())
            ->withAddress($this->address)
            ->withBill($this->bill)
            ->withConvenienceAmount($fee)
            ->withCurrency("USD")
            ->execute();
        
        $this->validateSuccessfulTransaction($result);
    }

    public function testClearLoadedBills()
    {
        $service = new BillPayService();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->billLoad);

        $loadedBill = new Bill();
        $loadedBill->setAmount($this->billLoad->getAmount());
        $loadedBill->setBillPresentment($this->billLoad->getBillPresentment());
        $loadedBill->setBillType("InvalidBillType");
        $loadedBill->setCustomer($this->billLoad->getCustomer());
        $loadedBill->setDueDate($this->billLoad->getDueDate());
        $loadedBill->setIdentifier1($this->billLoad->getIdentifier1());

        /** @var BillingResponse */
        $response = $service->clearBills();

        $this->assertNotNull($response);
    }

    public function testCreateRecurringScheduleSecondInstanceDateScheduleTypeRequiredNegative() 
    {
        $this->customer = new Customer();
        $this->customer->address = $this->address;
        $this->customer->email = "testemailaddress@e-hps.com";
        $this->customer->firstName = "Test";
        $this->customer->lastName = "Tester";
        $this->customer->homePhone = "555-555-4444";
        $this->customer->id = uniqid();
        $this->customer->create();

        /** @var RecurringPaymentMethod */
        $paymentMethod = $this->customer
            ->addPaymentMethod(uniqid(), $this->clearTextCredit)
            ->create();

        $this->bill->setBillType("Tax Payments");

        try {
            $recur = $paymentMethod->addSchedule(uniqid())
                ->withAmount(50.0)
                ->withBill($this->blindBill)
                ->withCustomer($this->customer)
                ->withStartDate(new \DateTime())
                ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2026-12-21'))
                ->withnumberOfPaymentsRemaining(27)
                ->withSecondInstanceDate(\DateTime::createFromFormat('Y-m-d', '2024-07-31'))
                ->withToken($paymentMethod->token)
                ->withPrimaryConvenienceAmount(5.0)
                ->withLastPrimaryConvenienceAmount(4.0)
                ->withRecurringAuthorizationType(RecurringAuthorizationType::UNASSIGNED)
                ->withInitialPaymentMethod(InitialPaymentMethod::CARD)
                ->create();

            $this->fail("Transaction should call ApiException");
        } catch (ApiException $e) {
            $this->assertTrue($e->getMessage() === 'Schedule Type is required to perform recurring transaction.');
        }
    }

    public function testCommitPreLoadedBillsException()
    {
        $service = new BillPayService();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->billLoad);

        $this->billLoad = new Bill();
        $this->billLoad->setAmount(50.0);
        $this->billLoad->setBillType("Tax Payments");
        $this->billLoad->setIdentifier1("12345");
        $this->billLoad->setIdentifier2("23456");
        $this->billLoad->setBillPresentment(BillPresentment::FULL);
        $this->billLoad->setCustomer($this->customer);

        try {
            /** @var BillingResponse */
            $fee = $service->commitPreloadedBills();
            $this->fail('Exception message to be thrown');
        } catch (GatewayException $e) {
            $this->assertTrue($e->getMessage() === 'An error occurred attempting to commit the preloaded bills');
        }
    }

    public function testTokenizeUsingCreditCardReturnsCardType(): void
    {
        $cardTypeVisa = "VISA";
        $cardTypeDiscover = "DISC";
        $cardTypeMasterCard = "MC";
        $cardTypeAmericanExpress = "AMEX";

        $now = new DateTime('now');

        $clearTextCreditVisa = new CreditCardData();
        $clearTextCreditVisa->number = "4444444444444448";
        $clearTextCreditVisa->expMonth = $now->format('n');
        $clearTextCreditVisa->expYear = $now->format('Y');
        $clearTextCreditVisa->cvn = "123";
        $clearTextCreditVisa->cardHolderName = "Test Tester";

        $clearTextCreditDiscover = new CreditCardData();
        $clearTextCreditDiscover->number = "6011000000000087";
        $clearTextCreditDiscover->expMonth = $now->format('n');
        $clearTextCreditDiscover->expYear = $now->format('Y');
        $clearTextCreditDiscover->cvn = "123";
        $clearTextCreditDiscover->cardHolderName = "Test Tester";

        $clearTextCreditMasterCard = new CreditCardData();
        $clearTextCreditMasterCard->number = "5425230000004415";
        $clearTextCreditMasterCard->expMonth = $now->format('n');
        $clearTextCreditMasterCard->expYear = $now->format('Y');
        $clearTextCreditMasterCard->cvn = "123";
        $clearTextCreditMasterCard->cardHolderName = "Test Tester";
        
        $clearTextCreditAmericanExpress = new CreditCardData();
        $clearTextCreditAmericanExpress->number = "374101000000608";
        $clearTextCreditAmericanExpress->expMonth = $now->format('n');
        $clearTextCreditAmericanExpress->expYear = $now->format('Y');
        $clearTextCreditAmericanExpress->cvn = "123";
        $clearTextCreditAmericanExpress->cardHolderName = "Test Tester";

        // VISA
        $tokenResponseVisa = $clearTextCreditVisa->verify()
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withRequestMultiUseToken(true)
            ->execute();

        $clearTextCreditVisa->token = $tokenResponseVisa->token;
        $tokenInfoResponseVisa = $clearTextCreditVisa->getTokenInformation();

        $this->assertNotNull($tokenInfoResponseVisa);
        $this->assertNotNull($tokenInfoResponseVisa->tokenData);
        $this->assertEquals($cardTypeVisa, $tokenInfoResponseVisa->cardType);
        $this->assertEquals($cardTypeVisa, $tokenInfoResponseVisa->cardDetails->brand);

        // Discover
        $tokenResponseDiscover = $clearTextCreditDiscover->verify()
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withRequestMultiUseToken(true)
            ->execute();

        $clearTextCreditDiscover->token = $tokenResponseDiscover->token;
        $tokenInfoResponseDiscover = $clearTextCreditDiscover->getTokenInformation();

        $this->assertNotNull($tokenInfoResponseDiscover);
        $this->assertNotNull($tokenInfoResponseDiscover->tokenData);
        $this->assertEquals($cardTypeDiscover, $tokenInfoResponseDiscover->cardType);
        $this->assertEquals($cardTypeDiscover, $tokenInfoResponseDiscover->cardDetails->brand);

        // Master Card
        $tokenResponseMasterCard = $clearTextCreditMasterCard->verify()
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withRequestMultiUseToken(true)
            ->execute();

        $clearTextCreditMasterCard->token = $tokenResponseMasterCard->token;
        $tokenInfoResponseMasterCard = $clearTextCreditMasterCard->getTokenInformation();

        $this->assertNotNull($tokenInfoResponseMasterCard);
        $this->assertNotNull($tokenInfoResponseMasterCard->tokenData);
        $this->assertEquals($cardTypeMasterCard, $tokenInfoResponseMasterCard->cardType);
        $this->assertEquals($cardTypeMasterCard, $tokenInfoResponseMasterCard->cardDetails->brand);

        // American Express
        $tokenResponseAmericanExpress = $clearTextCreditAmericanExpress->verify()
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withRequestMultiUseToken(true)
            ->execute();

        $clearTextCreditAmericanExpress->token = $tokenResponseAmericanExpress->token;
        $tokenInfoResponseAmericanExpress = $clearTextCreditAmericanExpress->getTokenInformation();

        $this->assertNotNull($tokenInfoResponseAmericanExpress);
        $this->assertNotNull($tokenInfoResponseAmericanExpress->tokenData);
        $this->assertEquals($cardTypeAmericanExpress, $tokenInfoResponseAmericanExpress->cardType);
        $this->assertEquals($cardTypeAmericanExpress, $tokenInfoResponseAmericanExpress->cardDetails->brand);
    }

    private function validateSuccessfulTransaction(Transaction $transaction): void
    {
        $transactionId = (int) $transaction->transactionId;

        $this->assertNotEquals($transactionId, 0);
    }

    /**
     * @param int
     * @return array<Bill>
     */
    private function makeNumberOfBills(int $number): array
    {
         /** @var array<Bill> */
         $bills = [];

         for ($i = 0; $i < $number; $i++)
         {
            $bill = new Bill();
            $bill->setAmount($this->billLoad->getAmount());
            $bill->setBillPresentment($this->billLoad->getBillPresentment());
            $bill->setBillType($this->billLoad->getBillType());
            $bill->setCustomer($this->billLoad->getCustomer());
            $bill->setDueDate($this->billLoad->getDueDate());
            $bill->setIdentifier1(sprintf("%s", $i));
            $bill->setIdentifier2(sprintf("%s", $i));

            array_push($bills, $bill);
         }
         
         return $bills;
    }

    private function getQuickPayConfig()
    {
        $config = new BillPayConfig();
        $config->setMerchantName("LeeCo");
        $config->setUsername("sdktest");
        $config->setPassword('$Test1234');
        $config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION;
        // Uncomment if you want to implement logger
        $config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
        ServicesContainer::configureService($config);
    }
}