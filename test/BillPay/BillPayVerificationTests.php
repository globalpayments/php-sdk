<?php

namespace GlobalPayments\Api\Test\BillPay\BillPay;

use DateTime;
use Exception;
use GlobalPayments\Api\Entities\{
    Address, 
    Customer,
    HostedPaymentData,
    Transaction
};
use GlobalPayments\Api\Entities\BillPay\Bill;
use GlobalPayments\Api\Entities\Enums\{
    AccountType,
    BillPresentment,
    CheckType,
    HostedPaymentType,
    PaymentMethodType,
    PaymentMethodUsageMode,
    SecCode,
    ServiceEndpoints
};
use GlobalPayments\Api\Entities\Exceptions\{
    ApiException,
    BuilderException,
    GatewayException
};
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\PaymentMethods\{
    CreditCardData,
    ECheck
};
use GlobalPayments\Api\ServiceConfigs\Gateways\BillPayConfig;
use GlobalPayments\Api\Services\BillPayService;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\{SampleRequestLogger, Logger};
use PHPUnit\Framework\TestCase;

class BillPayVerificationTests extends TestCase
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

        $dateTime = new DateTime();

        $this->clearTextCredit = new CreditCardData();
        $this->clearTextCredit->number = "4444444444444448";
        $this->clearTextCredit->expMonth = $dateTime->format('m');
        $this->clearTextCredit->expYear = $dateTime->format('Y');
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
        $this->customer->company = "Test Company";
        $this->customer->middleName = "Testing";

        $this->bill = new Bill();
        $this->bill->setAmount("50");
        $this->bill->setIdentifier1("12345");

        $bill1 = new Bill();
        $bill1->setBillType("Bill Payment");
        $bill1->setIdentifier1("123");
        $bill1->setAmount("10");

        $bill2 = new Bill();
        $bill2->setBillType("Bill Payment");
        $bill2->setIdentifier1("321");
        $bill2->setAmount("10");

        $this->bills = [$bill1, $bill2];

        $now = new DateTime('now');
        $now->modify('+3 days');

        $this->billLoad = new Bill();
        $this->billLoad->setAmount("50");
        $this->billLoad->setBillType("Bill Payment");
        $this->billLoad->setIdentifier1("12345");
        $this->billLoad->setIdentifier2("23456");
        $this->billLoad->setBillPresentment(BillPresentment::FULL);
        $this->billLoad->setDueDate($now);
        $this->billLoad->setCustomer($this->customer);

        $now = new DateTime('now');
        $now->modify('+1 days');

        $this->blindBill = new Bill();
        $this->blindBill->setAmount("50");
        $this->blindBill->setBillType("Bill Payment");
        $this->blindBill->setIdentifier1("12345");
        $this->blindBill->setIdentifier2("23456");
        $this->blindBill->setBillPresentment(BillPresentment::FULL);
        $this->blindBill->setDueDate($now);
        $this->blindBill->setCustomer($this->customer);
    }

    protected function getConfig()
    {
        $config = new BillPayConfig();
        /*$config->setMerchantName("Dev_Exp_Team_Merchant");
        $config->setUsername("dev_exp_fh");
        $config->setPassword("devexpteam_R0cks!");*/
        $config->setMerchantName("BillPayPHPStaging");
        $config->setUsername("BillPayPHPAPI");
        $config->setPassword("wRApHo?5swUR!!");
        $config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION;
        // Uncomment if you want to implement logger
        //$config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
        return $config;
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    #region Authorization Builder Cases
    public function testTokenize_UsingCreditCard_ReturnsTokenInformation()
    {
        $tokenResponse = $this->clearTextCredit->verify()
            ->withAddress($this->address)
            ->withCustomerData($this->customer)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->clearTextCredit->token = $tokenResponse->token;
        $tokenInfoResponse = $this->clearTextCredit->getTokenInformation();

        $this->assertNotNull($tokenInfoResponse);
        $this->assertNotNull($tokenInfoResponse->tokenData);
    }

    public function testTokenize_UsingCreditCard_ReturnsCardType()
    {
        $cardTypeVisa = "VISA";
        $cardTypeDiscover = "DISC";
        $cardTypeMasterCard = "MC";
        $cardTypeAmericanExpress = "AMEX";

        $now = new DateTime('now');

        $clearTextCreditVisa = new CreditCardData();
        $clearTextCreditVisa->number = "4444444444444448";
        $clearTextCreditVisa->expMonth = $now->format('m');
        $clearTextCreditVisa->expYear = $now->format('Y');
        $clearTextCreditVisa->cvn = "123";
        $clearTextCreditVisa->cardHolderName = 'Test Tester';

        $clearTextCreditDiscover = new CreditCardData();
        $clearTextCreditDiscover->number = "6011000000000087";
        $clearTextCreditDiscover->expMonth = $now->format('m');
        $clearTextCreditDiscover->expYear = $now->format('Y');
        $clearTextCreditDiscover->cvn = "123";
        $clearTextCreditDiscover->cardHolderName = 'Test Tester';

        $clearTextCreditMasterCard = new CreditCardData();
        $clearTextCreditMasterCard->number = "5425230000004415";
        $clearTextCreditMasterCard->expMonth = $now->format('m');
        $clearTextCreditMasterCard->expYear = $now->format('Y');
        $clearTextCreditMasterCard->cvn = "123";
        $clearTextCreditMasterCard->cardHolderName = 'Test Tester';

        $clearTextCreditAmericanExpress = new CreditCardData();
        $clearTextCreditAmericanExpress->number = "374101000000608";
        $clearTextCreditAmericanExpress->expMonth = $now->format('m');
        $clearTextCreditAmericanExpress->expYear = $now->format('Y');
        $clearTextCreditAmericanExpress->cvn = "123";
        $clearTextCreditAmericanExpress->cardHolderName = 'Test Tester';

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

    public function testTokenize_UsingCreditCard_ReturnsToken()
    {
        $address = new Address();
        $address->postalCode = '12345';

        $response = $this->clearTextCredit->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response->token);
        $this->assertNotEmpty($response->token);
    }

    public function testTokenize_UsingCreditCard_WithCustomerData_ReturnsToken()
    {
        $token = $this->clearTextCredit->tokenizeWithVerifyCardUsageAddressCustomerConfig(
            true, 
            $this->address, 
            $this->customer
        );
        $this->assertNotNull($token);
        $this->assertNotEmpty($token);
    }

    public function testUpdateTokenExpiry_UsingCreditCardToken_DoesNotThrow()
    {
        $address = new Address();
        $address->postalCode = '12345';

        $response = $this->clearTextCredit->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response->token);
        $this->assertNotEmpty($response->token);

        try {
            $this->clearTextCredit->token = $response->token;
            $this->clearTextCredit->expMonth = '12';
            $this->clearTextCredit->expYear = '2028';

            $this->clearTextCredit->updateTokenExpiry();
        } catch(Exception $ex) {
            $this->fail($ex->getMessage());
        }
    }

    public function testTokenize_UsingACH_ReturnsToken()
    {
        $token = $this->ach->tokenize();

        $this->assertNotNull($token);
        $this->assertNotEmpty($token);
    }

    public function testTokenize_UsingACH_WithCustomerData_ReturnsToken()
    {
        $token = $this->ach->tokenizeWithCustomerData(
            true,
            $this->address,
            $this->customer
        );

        $this->assertNotNull($token);
        $this->assertNotEmpty($token);
    }

    public function testTokenize_UsingACH_ReturnsTokenInformation()
    {
        $token = $this->ach->tokenize();

        $this->ach->token = $token;

        $tokenInfoResponse = $this->ach->getTokenInformation();

        $this->assertNotNull($tokenInfoResponse);
        $this->assertNotEmpty($tokenInfoResponse);

        $this->assertNotNull($tokenInfoResponse->tokenData);
        $this->assertNotEmpty($tokenInfoResponse->tokenData);
    }

    public function testCharge_UsingTokenizedCreditCard_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();
        $address = new Address();
        $address->postalCode = "12345";

        $response = $this->clearTextCredit->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertFalse(empty($response->token));

        $token = $response->token;
        $fee = $service->calculateConvenienceAmount(
            $this->clearTextCredit, 
            $this->bill->getAmount()
        );

        $paymentMethod = new CreditCardData();
        $paymentMethod->token = $token;
        $paymentMethod->expMonth = $this->clearTextCredit->expMonth;
        $paymentMethod->expYear = $this->clearTextCredit->expYear;

        $this->assertFalse(empty($token));

        $this->RunAndValidateTransaction(function() use($paymentMethod, $fee) : Transaction {
            return $paymentMethod
                ->charge($this->bill->getAmount())
                ->withAddress($this->address)
                ->withBill($this->bill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->execute();
        });
    }

    public function testCharge_UsingSingleUseToken_ReturnsSuccessfulTransaction()
    {
        $this->getQuickPayConfigLeeco();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $paymentMethod = new CreditCardData();
        $paymentMethod->number = "5454545454545454";
        $paymentMethod->expMonth = '05';
        $paymentMethod->expYear = '2027';
        $paymentMethod->cvn = '123';
        $paymentMethod->cardHolderName = "Hank Hill";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/' to generate token
        $paymentMethod->token = "E8352F21-B233-48B1-B0E1-3685674CE0AC";

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount(
            $paymentMethod, 
            $bill->getAmount()
        );
        
        $transaction = $this->RunAndValidateTransaction(function() use($paymentMethod, $fee, $bill, $address, $customer): Transaction {
            return $paymentMethod
                ->charge($bill->getAmount())
                ->withAddress($address)
                ->withCustomerData($customer)
                ->withBill($bill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();
        });

        $this->assertNotNull($transaction);
    }

    public function testCharge_UsingSingleUseToken_ReturnsSuccessfulTransactionWithToken()
    {
        $this->getQuickPayConfigLeeco();

        $address = new Address();
        $address->postalCode = "36832";

        $customer = new Customer();
        $customer->address = $address;

        $bill = new Bill();
        $bill->setAmount("350");
        $bill->setIdentifier1("12345");
        $bill->setBillType("Tax Payments");

        $paymentMethod = new CreditCardData();
        $paymentMethod->number = "5454545454545454";
        $paymentMethod->expMonth = '05';
        $paymentMethod->expYear = '2027';
        $paymentMethod->cvn = '123';
        $paymentMethod->cardHolderName = "Hank Hill";
        // need to use diff token everytime
        // use this link 'https://staging.heartlandpaymentservices.net/QuickPayWeb/' to generate token
        $paymentMethod->token = "2ED86BB1-4A4B-44AC-B2D9-A3DE37C651DA";

        $service = new BillPayService();
        $fee = $service->calculateConvenienceAmount(
            $paymentMethod, 
            $bill->getAmount()
        );

        $transaction = $this->RunAndValidateTransaction(function() use($paymentMethod, $fee, $bill, $address, $customer): Transaction {
            return $paymentMethod
                ->charge($bill->getAmount())
                ->withAddress($address)
                ->withCustomerData($customer)
                ->withBill($bill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->withRequestMultiUseToken(true)
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();
        });

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);
    }

    public function testCharge_UsingTokenizedACH_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();
        $result = $this->ach->tokenize();
        $fee = $service->calculateConvenienceAmount(
            $this->ach, 
            $this->bill->getAmount()
        );
        $paymentMethod = new ECheck();
        $paymentMethod->accountType = AccountType::CHECKING;
        $paymentMethod->checkType = CheckType::BUSINESS;
        $paymentMethod->secCode = SecCode::WEB;
        $paymentMethod->checkHolderName = 'Tester';
        $paymentMethod->token = $result;

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);

        $this->RunAndValidateTransaction(function() use($paymentMethod, $fee): Transaction 
        {
            return $paymentMethod
                ->charge($this->bill->getAmount())
                ->withBill($this->bill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->withAddress($this->address)
                ->execute();
        });
    }

    public function testCharge_UsingTokenFromPreviousPayment_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();

        $clearTextCredit = new CreditCardData();
        $clearTextCredit->number = '372700699251018';
        $clearTextCredit->expMonth = '12';
        $clearTextCredit->expYear = '2027';
        $clearTextCredit->cardHolderName = 'Test Tester';

        $newBill = new Bill();
        $newBill->setAmount("65");
        $newBill->setBillType("Bill Payment");
        $newBill->setIdentifier1("12345");
        $newBill->setIdentifier2("23456");

        $fee = $service->calculateConvenienceAmount(
            $clearTextCredit, 
            $newBill->getAmount()
        );

        $transaction = $this->RunAndValidateTransaction(function() use ($fee, $clearTextCredit, $newBill): Transaction 
        {
            return $clearTextCredit
                ->charge($newBill->getAmount())
                ->withAddress($this->address)
                ->withBill($newBill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->withRequestMultiUseToken(true)
                ->execute();
        });

        $this->assertNotNull($transaction->token);
        $this->assertNotEmpty($transaction->token);

        $this->RunAndValidateTransaction(function() use ($transaction, $fee, $clearTextCredit): Transaction 
        {
            $tokenizedCard = new CreditCardData();
            $tokenizedCard->token = $transaction->token;
            $tokenizedCard->expYear = $clearTextCredit->expYear;
            $tokenizedCard->expMonth = $clearTextCredit->expMonth;

            return $tokenizedCard->charge($this->billLoad->getAmount())
                ->withBill($this->billLoad)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->execute();
        });
    }

    public function testCharge_WithoutAddingBills_ThrowsValidationException()
    {
        try {
            $result = $this->clearTextCredit
                ->charge(50.0)
                ->withCurrency('USD')
                ->withConvenienceAmount(3.0)
                ->execute();

            $this->fail('Success Transaction');
        } catch(BuilderException $e) {
            $this->assertTrue($e instanceof BuilderException);
        }
    }

    public function testCharge_WithMismatchingAmounts_ThrowsValidationException()
    {
        try {
            $result = $this->clearTextCredit
                ->charge(60.0)
                ->withBills($this->bills)
                ->withCurrency('USD')
                ->execute();
            
            $this->fail('Success Transaction');  
        } catch(BuilderException $e) {
            $this->assertTrue($e instanceof BuilderException);
        }
    }
    #endregion

    #region Management Builder Cases
    public function testReversePayment_WithPreviousTransaction_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();

        $clearTextCredit = new CreditCardData();
        $clearTextCredit->number = '4012002000060016';
        $clearTextCredit->expMonth = 12;
        $clearTextCredit->expYear = 2027;
        $clearTextCredit->cvn = '123';
        $clearTextCredit->cardHolderName = 'Test Tester';

        $fee = $service->calculateConvenienceAmount(
            $clearTextCredit, 
            $this->bill->getAmount()
        );

        // Make transaction to reverse
        $transaction = $this->RunAndValidateTransaction(function() use($fee, $clearTextCredit): Transaction {
            return $clearTextCredit
                ->charge($this->bill->getAmount())
                ->withAddress($this->address)
                ->withBill($this->bill)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->execute();
        });

        // Now reverse it
        $reversal = $this->RunAndValidateTransaction(function() use($transaction, $fee): Transaction {
            return Transaction::fromId(
                $transaction->transactionId, 
                null, 
                PaymentMethodType::CREDIT
            )
            ->reverse($this->bill->getAmount())
            ->withConvenienceAmount($fee)
            ->execute();
        });
    }

    public function testReversePayment_WithPreviousMultiBillTransaction_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();
        $totalAmount = array_sum(
            array_map(fn($bill) => $bill->getAmount(), $this->bills)
        );
        $fee = $service->calculateConvenienceAmount(
            $this->clearTextCredit, 
            $totalAmount
        );

        // Make transaction to reverse
        $transaction = $this->RunAndValidateTransaction(function() use($totalAmount, $fee): Transaction {
            return $this->clearTextCredit
                ->charge($totalAmount)
                ->withAddress($this->address)
                ->withBills($this->bills)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->execute();
        });

        // Now reverse it
        $reversal = $this->RunAndValidateTransaction(function() use($transaction): Transaction {
            return Transaction::fromId(
                $transaction->transactionId,
                null,
                PaymentMethodType::CREDIT
            );
        });
    }

    public function testPartialReversal_WithCreditCard_ReturnsSuccessfulTransaction()
    {
        $service = new BillPayService();
        $totalAmount = array_sum(
            array_map(fn($bill) => $bill->getAmount(), $this->bills)
        );
        $clearTextCredit = new CreditCardData();
        $clearTextCredit->number = '372700699251018';
        $clearTextCredit->expMonth = 12;
        $clearTextCredit->expYear = 2027;
        $clearTextCredit->cvn = '1234';
        $clearTextCredit->cardHolderName = 'Test Tester';

        $fee = $service->calculateConvenienceAmount(
            $clearTextCredit, 
            $totalAmount
        );

        // Make transaction to reverse
        $transaction = $this->RunAndValidateTransaction(function() use($totalAmount, $fee, $clearTextCredit): Transaction {
            return $clearTextCredit
                ->charge($totalAmount)
                ->withAddress($this->address)
                ->withBills($this->bills)
                ->withPaymentMethod($clearTextCredit)
                ->withConvenienceAmount($fee)
                ->withCurrency('USD')
                ->execute();
        });

        // Now reverse it
        $reversal = $this->RunAndValidateTransaction(
            function() use($transaction, $service, $totalAmount, $fee, $clearTextCredit): Transaction {
                /** @var Bill[] */
                $billsToPartiallyReverse = array_map(function($bill) {
                    $newBill = new Bill();
                    $newBill->setBillType($bill->getBillType());
                    $newBill->setIdentifier1($bill->getIdentifier1());
                    $newBill->setAmount($bill->getAmount() - 5.0);
                    return $newBill;
                }, $this->bills);

                $newFees = $service->calculateConvenienceAmount(
                    $clearTextCredit,
                    $totalAmount - 10.0
                );

                $reversalTransaction = Transaction::fromId(
                    $transaction->transactionId,
                    null,
                    PaymentMethodType::CREDIT
                );

                return $reversalTransaction ->reverse($totalAmount - 10.0)
                    ->withBills($billsToPartiallyReverse)
                    ->withConvenienceAmount($fee - $newFees)
                    ->execute();
            }
        );
    }
    #endregion

    #region Billing Builder Cases
    public function testLoadHostedPayment_WithMakePaymentType_ReturnsIdentifier()
    {
        $service = new BillPayService();
        $data = new HostedPaymentData();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->blindBill);

        $address = new Address();
        $address->streetAddress1 = '123 Drive';
        $address->postalCode = '12345';

        $data->bills = $bills;
        $data->customerAddress = $address;
        $data->customerEmail = 'test@tester.com';
        $data->customerFirstName = 'Test';
        $data->customerLastName = 'Tester';
        $data->hostedPaymentType = HostedPaymentType::MAKE_PAYMENT;

        $response = $service->loadHostedPayment($data);

        $this->assertTrue(!empty($response->getPaymentIdentifier()));
    }

    public function testLoadHostedPayment_WithMakePaymentReturnToken_ReturnsIdentifier()
    {
        $service = new BillPayService();
        $hostedPaymentData = new HostedPaymentData();

        /** @var array<Bill> */
        $bills = [];
        array_push($bills, $this->blindBill);

        $address = new Address();
        $address->streetAddress1 = '123 Drive';
        $address->city = 'Auburn';
        $address->state = 'AL';
        $address->postalCode = '36830';
        $address->countryCode = 'US';

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

    public function testLoadHostedPayment_WithoutBills_ThrowsValidationException()
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
            $this->assertTrue($e instanceof BuilderException);
        } 
    }

    public function testLoadHostedPayment_WithoutPaymentType_ThrowsValidationException()
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
            $this->assertTrue($e instanceof BuilderException);
        } 
    }

    public function testLoad_WithOneBill_DoesNotThrow()
    {
        try {
            $this->getQuickPayConfig();

            $service = new BillPayService();

            /** @var array<Bill> */
            $bills = [];
            array_push($bills, $this->billLoad);

            $service->loadBills($bills);
            
            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        } 
    }

    public function testLoad_WithOneThousandBills_DoesNotThrow()
    {
        try {
            $this->getQuickPayConfig();

            $service = new BillPayService();

            $service->loadBills($this->makeNumberOfBills(1000));

            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        } 
    }

    public function testLoad_WithFiveThousandBills_DoesNotThrow()
    {
        try {
            $this->getQuickPayConfig();

            $service = new BillPayService();

            $service->loadBills($this->makeNumberOfBills(5000));

            $this->assertTrue(true);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        } 
    }

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

    public function testLoad_WithDuplicateBills_ThrowsGatewayException()
    {
        $service = new BillPayService();

        /** @var array<Bill> */
        $bills = [];

        array_push($bills, $this->billLoad);
        array_push($bills, $this->billLoad);

        $this->expectException(GatewayException::class);
        $service->loadBills($bills);
    }

    public function testLoad_WithInvalidBillType_ThrowsGatewayException()
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
        }catch (GatewayException $e){
            $this->assertTrue($e instanceof GatewayException);
        }
    }
    #endregion

    #region Recurring Builder Cases
    public function testCreate_Customer_ReturnsCustomer()
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

    public function testUpdate_Customer_ReturnsCustomer()
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

    public function testDelete_Customer_ReturnsCustomer()
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

    public function testCreate_CustomerAccount_ReturnsPaymentMethod()
    {
        try {
            $customer = new Customer();
            $customer->firstName = "Integration";
            $customer->lastName = "Customer";
            $customer->email = "test.test@test.com";
            $customer->id = uniqid();
            $customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
           
            $this->assertFalse($paymentMethod->key === "");
            $this->assertFalse($paymentMethod->key === null);
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }   
    }

    public function testUpdate_CustomerAccount_ReturnsSuccess()
    {
        try {
            $customer = new Customer();
            $customer->firstName = "Account";
            $customer->lastName = "Update";
            $customer->email = "account.update@test.com";
            $customer->id = uniqid();
            $customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $customer
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

    public function testDelete_CustomerAccount_ReturnsSuccess()
    {
        try {
            $customer = new Customer();
            $customer->firstName = "Account";
            $customer->lastName = "Delete";
            $customer->email = "account.delete@test.com";
            $customer->id = uniqid();
            $customer->create();

            /** @var RecurringPaymentMethod */
            $paymentMethod = $customer
                ->addPaymentMethod(uniqid(), $this->clearTextCredit)
                ->create();
           
            $this->assertFalse($paymentMethod->key === "");
            $this->assertFalse($paymentMethod->key === null);

            $paymentMethod->delete();
        } catch (ApiException $e) {
            $this->fail($e->getMessage());
        }   
    }

    public function testDelete_NonexistingCustomer_ThrowsApiException()
    {
        $customer = new Customer();
        $customer->firstName = "Incog";
        $customer->lastName = "Anony";
        $customer->id = "DoesntExist";

        $this->expectException(ApiException::class);
        $customer->delete();
    }
    #endregion

    #region Report Builder Cases
    public function testGetTransactionByOrderID_SingleBill()
    {
        $service = new BillPayService();
        
        $address = new Address();
        $address->postalCode = '12345';

        $response = $this->clearTextCredit->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response->token);
        $this->assertNotEmpty($response->token);

        $token = $response->token;
        $fee = $service->calculateConvenienceAmount(
            $this->clearTextCredit,
            $this->bill->getAmount()
        );

        $paymentMethod = new CreditCardData();
        $paymentMethod->token = $token;
        $paymentMethod->expMonth = $this->clearTextCredit->expMonth;
        $paymentMethod->expYear = $this->clearTextCredit->expYear;

        $this->assertNotNull($token);
        $this->assertNotEmpty($token);

        $orderID = uniqid();
        $transactionResponse = $this->RunAndValidateTransaction(
            function() use($paymentMethod, $address, $fee, $orderID): Transaction {
                return $paymentMethod
                    ->charge($this->bill->getAmount())
                    ->withAddress($address)
                    ->withBill($this->bill)
                    ->withConvenienceAmount($fee)
                    ->withOrderId($orderID)
                    ->withCurrency('USD')
                    ->execute();
            }
        );

        /** @var TransactionSummary */
        $summary = ReportingService::transactionDetail($orderID)->execute();
        $this->assertNotNull($summary);
    }

    public function testGetTransactionByOrderID_MultipleBills()
    {
        $service = new BillPayService();
        $totalAmount = array_sum(
            array_map(fn($bill) => $bill->getAmount(), $this->bills)
        );
        $fee = $service->calculateConvenienceAmount(
            $this->clearTextCredit,
            $totalAmount
        );

        $orderID = uniqid();
        $transactionResponse = $this->RunAndValidateTransaction(
            function() use($totalAmount, $fee, $orderID): Transaction {
                return $this->clearTextCredit
                    ->charge($totalAmount)
                    ->withAddress($this->address)
                    ->withBills($this->bills)
                    ->withConvenienceAmount($fee)
                    ->withOrderId($orderID)
                    ->withCurrency('USD')
                    ->execute();
            }
        );

        /** @var TransactionSummary */
        $summary = ReportingService::transactionDetail($orderID);
        $this->assertNotNull($summary);
    }
    #endregion

    #region Helpers
    /**
     * Encapsulates the standard test framework for running a succesful transaction.
     *
     * @param callable $transactionAction A method that executes and returns a payment
     * @return Transaction The transaction generated by transactionAction
     *
     */
    private function RunAndValidateTransaction(callable $transactionAction): Transaction
    {
        /** @var Transaction */
        $transaction = null;

        try {
            $transaction = $transactionAction();

            $this->ValidateSuccesfulTransaction($transaction);
        } catch (GatewayException $gex) {
            $this->fail($gex->responseCode . ' - ' . $gex->responseMessage);
        } catch (ApiException $aex) {
            $this->fail($aex->getMessage());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        return $transaction;
    }

    private function ValidateSuccesfulTransaction($transaction)
    {
        $isValidTransactionId = filter_var($transaction->transactionId, FILTER_VALIDATE_INT);

        assert($isValidTransactionId, 'Transaction Id is not an integer');

        $this->assertNotEquals((int)$transaction->transactionId !== 0, $transaction->responseMessage);
    }
    #endregion

    private function getQuickPayConfig() 
    {
        $config = new BillPayConfig();
        $config->setMerchantName("BillPayPHPLookupStaging");
        $config->setUsername("BillPayPHPAPI");
        $config->setPassword('wRApHo?5swUR!!');
        $config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION;
        // Uncomment if you want to implement logger
        //$config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
        ServicesContainer::configureService($config);
    }

    private function getQuickPayConfigLeeco()
    {
        $config = new BillPayConfig();
        $config->setMerchantName("LeeCo");
        $config->setUsername("sdktest");
        $config->setPassword('$Test1234');
        $config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION;
        // Uncomment if you want to implement logger
        //$config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
        ServicesContainer::configureService($config);
    }
}