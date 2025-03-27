
# Billpay Feature - Transaction

# Configuration setup
```php
$config = new BillPayConfig();
$config->setMerchantName("Dev_Exp_Team_Merchant");
$config->setUsername("user_name");
$config->setPassword("password");
$config->serviceUrl = ServiceEndpoints::BILLPAY_CERTIFICATION; // BILLPAY_PRODUCTION
// Uncomment if you want to implement logger
//$config->requestLogger = new SampleRequestLogger(new Logger("billpay-logs"));
return $config;
```

Below are the list of `Billpay` transaction implemented in PHP SDK


## MakeBlindPayment

```php
CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

Bill $bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

try {
    $service = new BillPayService();
    $fee = $service->calculateConvenienceAmount($clearTextCredit, $bill->getAmount());

    $result = $clearTextCredit->charge($bill->getAmount())
        ->withAddress($this->address)
        ->withBill($this->bill)
        ->withConvenienceAmount($fee)
        ->withCurrency("USD")
        ->execute();

    var_dump($result) // returns `Transaction`
} catch (ApiException $e) {
    // handle errors
}
```

## MakeBlindPaymentReturnToken
```php

$clearTextCreditVisa = new CreditCardData();
$clearTextCreditVisa->number = "4444444444444448";
$clearTextCreditVisa->expMonth = $now->format('n');
$clearTextCreditVisa->expYear = $now->format('Y');
$clearTextCreditVisa->cvn = "123";
$clearTextCreditVisa->cardHolderName = $cardHolderName;

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345"

$customer = new Customer();
$customer->address = $this->address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

$bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

try {
    $tokenResponseVisa = $clearTextCreditVisa->charge($bill->getAmount())
        ->withAddress($address)
        ->withCustomerData($customer)
        ->withBill($bill)
        ->withCurrency("USD")
        ->withRequestMultiUseToken(true)
        ->execute();

    var_dump($result) // returns `Transaction`
} catch (ApiException $e) {
    // handle errors
}
```

## MakePayment
```php
/// add to config
/// $config->setUseBillRecordLookup(true);

CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

Bill $bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

try {
    $service = new BillPayService();
    $fee = $service->calculateConvenienceAmount($clearTextCredit, $bill->getAmount());

    $result = $clearTextCredit->charge($bill->getAmount())
        ->withAddress($this->address)
        ->withBill($this->bill)
        ->withConvenienceAmount($fee)
        ->withCurrency("USD")
        ->execute();

    var_dump($result) // returns `Transaction`
} catch (ApiException $e) {
    // handle errors
}
```

## MakePaymentReturnToken
```php
/// add to config
/// $config->setUseBillRecordLookup(true);

$clearTextCreditVisa = new CreditCardData();
$clearTextCreditVisa->number = "4444444444444448";
$clearTextCreditVisa->expMonth = $now->format('n');
$clearTextCreditVisa->expYear = $now->format('Y');
$clearTextCreditVisa->cvn = "123";
$clearTextCreditVisa->cardHolderName = $cardHolderName;

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345"

$customer = new Customer();
$customer->address = $this->address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

$bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

try {
    $tokenResponseVisa = $clearTextCreditVisa->charge($bill->getAmount())
        ->withAddress($address)
        ->withCustomerData($customer)
        ->withBill($bill)
        ->withCurrency("USD")
        ->withRequestMultiUseToken(true)
        ->execute();

    var_dump($result) // returns `Transaction`
} catch (ApiException $e) {
    // handle errors
}
```

## MakeQuickPayBlindPayment
```php
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
$ach->token = "[token here]";

try {
    /** @var Transaction */
    $result = $ach->charge($bill->getAmount())
        ->withAddress($address)
        ->withCustomerData($customer)
        ->withBill($bill)
        ->withConvenienceAmount(2.65)
        ->withCurrency("USD")
        ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
        ->execute();

    var_dump($result);
} catch (ApiException $e) {
    // handle errors
}
```

## MakeQuickPayBlindPaymentReturnToken
```php
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
$ach->token = "[token here]";


try {
    $result = $ach->charge($bill->getAmount())
        ->withCurrency("USD")
        ->withAddress($address)
        ->withBill($bill)
        ->withConvenienceAmount(2.65)
        ->withCustomerData($customer)
        ->withRequestMultiUseToken(true)
        ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
        ->execute();

    var_dump($result);
} catch (ApiException $e) {
    // handle errors
}
```

## ClearLoadedBills
```php
$service = new BillPayService();

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345";

$customer = new Customer();
$customer->address = $address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

$billLoad = new Bill();
$billLoad->setAmount("50");
$billLoad->setBillType("Tax Payments");
$billLoad->setIdentifier1("12345");
$billLoad->setIdentifier2("23456");
$billLoad->setBillPresentment(BillPresentment::FULL);
$billLoad->setDueDate($now);
$billLoad->setCustomer($customer);

/** @var array<Bill> */
$bills = [];
array_push($bills, $billLoad);

$loadedBill = new Bill();
$loadedBill->setAmount($billLoad->getAmount());
$loadedBill->setBillPresentment($billLoad->getBillPresentment());
$loadedBill->setBillType("InvalidBillType");
$loadedBill->setCustomer($billLoad->getCustomer());
$loadedBill->setDueDate($billLoad->getDueDate());
$loadedBill->setIdentifier1($billLoad->getIdentifier1());

/** @var BillingResponse */
$response = $service->clearBills();
```

## CommitPreloadedBills
```php
$service = new BillPayService();

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345";

$customer = new Customer();
$customer->address = $address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

$billLoad = new Bill();
$billLoad->setAmount("50");
$billLoad->setBillType("Tax Payments");
$billLoad->setIdentifier1("12345");
$billLoad->setIdentifier2("23456");
$billLoad->setBillPresentment(BillPresentment::FULL);
$billLoad->setCustomer($customer);

try {
    /** @var BillingResponse */
    $fee = $service->commitPreloadedBills();
} catch (GatewayException $e) {
    // handle errors            
}
```

## SaveCustomerAccount
```php
try {
    $customer = new Customer();
    $customer->firstName = "Integration";
    $customer->lastName = "Customer";
    $customer->email = "test.test@test.com";
    $customer->id = uniqid();
    $customer->create();

    /** @var RecurringPaymentMethod */
    $paymentMethod = $this->customer
        ->addPaymentMethod(uniqid(), $this->clearTextCredit)
        ->create();
    
    var_dump($paymentMethod);

} catch (ApiException $e) {
    // handle errors
}
```

## CreateSingleSignOnAccount
```php
try {
    $customer = new Customer();
    $customer->firstName = "Integration";
    $customer->lastName = "Customer";
    $customer->email = "test.test@test.com";
    $customer->id = uniqid();
    $customer->create();

    var_dump($customer);
} catch (ApiException $e) {
    // handle errors
}
```

## DeleteCustomerAccount
```php
try {
    $customer = new Customer();
    $customer->firstName = "Account";
    $customer->lastName = "Delete";
    $customer->email = "account.delete@test.com";
    $customer->id = uniqid();
    $customer->create();

    /** @var RecurringPaymentMethod */
    $paymentMethod = $this->customer
        ->addPaymentMethod(uniqid(), $clearTextCredit)
        ->create();
           

    $paymentMethod->delete();

} catch (ApiException $e) {
    // handle errors            
}   
```

## DeleteSingleSignOnAccount
```php
$clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

try {
    $customer = new Customer();
    $customer->firstName = "Account";
    $customer->lastName = "Delete";
    $customer->email = "account.delete@test.com";
    $customer->id = uniqid();
    $customer->create();

    /** @var RecurringPaymentMethod */
    $paymentMethod = $customer
        ->addPaymentMethod(uniqid(), $clearTextCredit)
        ->create();

    $paymentMethod->delete();
} catch (ApiException $e) {
    // handle errors
}   
```

## GetToken
```php
CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

$address = new Address();
$address->postalCode = "12345";

try {
    $response = $this->clearTextCredit->verify()
        ->withAddress($address)
        ->withRequestMultiUseToken(true)
        ->execute();
                
    var_dump($response->token);
} catch (ApiException $e) {
    // handle errors
}
```

## GetConvenienceFee
```php

CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

$bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

try {
    $service = new BillPayService();

    $fee = $service->calculateConvenienceAmount($clearTextCredit, $bill->getAmount());

    var_dump($fee);
} catch (ApiException $e) {
    // handle errors
}
```

## GetTokenInformation
```php
$clearTextCreditVisa = new CreditCardData();
$clearTextCreditVisa->number = "4444444444444448";
$clearTextCreditVisa->expMonth = $now->format('n');
$clearTextCreditVisa->expYear = $now->format('Y');
$clearTextCreditVisa->cvn = "123";
$clearTextCreditVisa->cardHolderName = $cardHolderName;

$bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345"

$customer = new Customer();
$customer->address = $this->address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

try {
    $tokenResponseVisa = $clearTextCreditVisa->charge($bill->getAmount())
        ->withAddress($address)
        ->withCustomerData($customer)
        ->withBill($bill)
        ->withCurrency("USD")
        ->withRequestMultiUseToken(true)
        ->execute();
    
    $clearTextCreditVisa->token = $tokenResponseVisa->token;
    $tokenInfoResponseVisa = $clearTextCreditVisa->getTokenInformation();

    var_dump($tokenInfoResponseVisa);
} catch (ApiException $e) {
    // handle errors
}
```

## LoadSecurePayDataExtended
```php
$service = new BillPayService();
$hostedPaymentData = new HostedPaymentData();

$customer = new Customer();
$customer->address = $this->address;
$customer->email = "testemailaddress@e-hps.com";
$customer->firstName = "Test";
$customer->lastName = "Tester";
$customer->homePhone = "555-555-4444";

$blindBill = new Bill();
$blindBill->setAmount("50");
$blindBill->setBillType("Tax Payments");
$blindBill->setIdentifier1("12345");
$blindBill->setIdentifier2("23456");
$blindBill->setBillPresentment(BillPresentment::FULL);
$blindBill->setDueDate($now);
$blindBill->setCustomer($customer);

/** @var array<Bill> */
$bills = [];
array_push($bills, $blindBill);

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
```

## UpdateCustomerAccount
```php
$clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

try {
    $customer = new Customer();
    $customer->firstName = "Account";
    $customer->lastName = "Update";
    $customer->email = "account.update@test.com";
    $customer->id = uniqid();
    $customer->create();

    /** @var RecurringPaymentMethod */
    $paymentMethod = $customer
        ->addPaymentMethod(uniqid(), $clearTextCredit)
        ->create();

    /** @var CreditCardData */
    $creditCardData = $paymentMethod->paymentMethod;
    $creditCardData->expYear = 2026;

    $paymentMethod->saveChanges();
} catch (ApiException $e) {
    // handle errors
}
```

## PreloadBills
```php
$blindBill = new Bill();
$blindBill->setAmount("50");
$blindBill->setBillType("Tax Payments");
$blindBill->setIdentifier1("12345");
$blindBill->setIdentifier2("23456");
$blindBill->setBillPresentment(BillPresentment::FULL);
$blindBill->setDueDate(new DateTime('now'));
$blindBill->setCustomer($customer);

try {
    $service = new BillPayService();

    /** @var array<Bill> */
    $bills = [];
    array_push($bills, $blindBill);

    $service->loadBills($bills);
} catch (ApiException $e) {
    // handle errors           
}
```

## ReversePayment
```php
$service = new BillPayService();

CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

$bill = new Bill();
$bill->setAmount("350");
$bill->setIdentifier1("12345");
$bill->setBillType("Tax Payments");

$fee = $service->calculateConvenienceAmount(
    $clearTextCredit, 
    $bill->getAmount()
);

$address = new Address();
$address->streetAddress1 = "1234 Test St";
$address->streetAddress2 = "Apt 201";
$address->city = "Auburn";
$address->state = "AL";
$address->country = "US";
$address->postalCode = "12345"

// Make transaction to reverse
$transaction = $this->clearTextCredit->charge($this->bill->getAmount())
    ->withAddress($this->address)
    ->withBill($this->bill)
    ->withConvenienceAmount($fee)
    ->withCurrency("USD")
    ->execute();

// Now reverse it
$reversal = new Transaction();
$reversal->transactionId = $transaction->transactionId;
$reversal->reverse($bill->getAmount())
    ->withConvenienceAmount($fee)
    ->execute();
```

## UpdateSingleSignOnAccount
```php
try {
    $customer = new Customer();
    $customer->firstName = "IntegrationUpdate";
    $customer->lastName = "Customer";
    $customer->email = "test.test@test.com";
    $customer->id = uniqid();
    $customer->create();

    $customer->firstName = "Updated";
    
    $customer->saveChanges();

} catch (ApiException $e) {
    // handle errors        
}
```

## CreateRecurringPayment
```php

try {
    CreditCardData $clearTextCredit = new CreditCardData();
    $clearTextCredit->number = "4444444444444448";
    $clearTextCredit->expMonth = "12";
    $clearTextCredit->expYear = "2025";
    $clearTextCredit->cvn = "123";
    $clearTextCredit->cardHolderName = "Test Tester";

    $address = new Address(); 
    $address->streetAddress1 = "1234 Test St";
    $address->streetAddress2 = "Apt 201";
    $address->city = "Auburn";
    $address->state = "AL";
    $address->country = "US";
    $address->postalCode = "12345";

    $customer = new Customer();
    $customer->firstName = "Account";
    $customer->lastName = "Update";
    $customer->email = "account.update@test.com";
    $customer->id = uniqid();
    $customer->create();

    $blindBill = new Bill();
    $blindBill->setAmount("50");
    $blindBill->setBillType("Tax Payments");
    $blindBill->setIdentifier1("12345");
    $blindBill->setIdentifier2("23456");
    $blindBill->setBillPresentment(BillPresentment::FULL);
    $blindBill->setDueDate(new DateTime('now'));
    $blindBill->setCustomer($customer);

    /** @var RecurringPaymentMethod */
    $paymentMethod = $customer
        ->addPaymentMethod(uniqid(), $clearTextCredit)
        ->create();
            
    $customer->address = $address;

    /** Schedule */
    $recur = $paymentMethod->addSchedule(uniqid())
        ->withAmount(50.0)
        ->withBill($blindBill)
        ->withCustomer($customer)
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
        
        var_dump($recur);
} catch (ApiException $e) {
    // handle errors
}
```

## UpdateTokenRequest
```php
$address = new Address();
$address->postalCode = '12345';

CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

$response = $clearTextCredit->verify()
    ->withAddress($address)
    ->withRequestMultiUseToken(true)
    ->execute();

try {
    $clearTextCredit->token = $response->token;
    $clearTextCredit->expMonth = '12';
    $clearTextCredit->expYear = '2022';

    $clearTextCredit->updateTokenExpiry();
} catch(Exception $ex) {
    // handle error here            
}
```

## GetTransactionByOrderIDRequest
```php
CreditCardData $clearTextCredit = new CreditCardData();
$clearTextCredit->number = "4444444444444448";
$clearTextCredit->expMonth = "12";
$clearTextCredit->expYear = "2025";
$clearTextCredit->cvn = "123";
$clearTextCredit->cardHolderName = "Test Tester";

$service = new BillPayService();
        
$address = new Address();
$address->postalCode = '12345';

$bill = new Bill();
$bill->setAmount("50");
$bill->setIdentifier1("12345");

$response = $clearTextCredit->verify()
    ->withAddress($address)
    ->withRequestMultiUseToken(true)
    ->execute();

$token = $response->token;
$fee = $service->calculateConvenienceAmount(
    $clearTextCredit,
    $bill->getAmount()
);

$paymentMethod = new CreditCardData();
$paymentMethod->token = $token;
$paymentMethod->expMonth = $clearTextCredit->expMonth;
$paymentMethod->expYear = $clearTextCredit->expYear;

$orderID = uniqid();

try{
    $paymentMethod
        ->charge($bill->getAmount())
        ->withAddress($address)
        ->withBill($bill)
        ->withConvenienceAmount($fee)
        ->withOrderId($orderID)
        ->withCurrency('USD')
        ->execute();
    
    $summary = ReportingService::transactionDetail($orderID)->execute();
    var_dump($summary);

} catch (ApiException $e) {
    // handle errors here
}
```