<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use PHPUnit\Framework\TestCase;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\Utils\GenerationUtils;

class RecurringTest extends TestCase
{
    /** @var string */
    private $todayDate = null;

    /** @var string */
    private $identifierBase = null;

    /** @var Customer */
    private static $customerPerson = null;

    /** @var Customer */
    private static $customerBusiness = null;

    /** @var RecurringPaymentMethod */
    private static $paymentMethodVisa = null;

    /** @var RecurringPaymentMethod */
    private static $paymentMethodMasterCard = null;

    /** @var RecurringPaymentMethod */
    private static $paymentMethodCheckPpd = null;

    /** @var RecurringPaymentMethod */
    private static $paymentMethodCheckCcd = null;

    /** @var Schedule */
    private static $scheduleVisa = null;

    /** @var Schedule */
    private static $scheduleMasterCard = null;

    /** @var Schedule */
    private static $scheduleCheckPpd = null;

    /** @var Schedule */
    private static $scheduleCheckCcd = null;

    private $enableCryptoUrl = true;

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getIdentifier($identifier)
    {
        return sprintf($this->identifierBase, $this->todayDate, $identifier);
    }

    /**
     * @return ServicesConfig
     */
    private function config()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }

    protected function setup()
    {
        ServicesContainer::configure($this->config());

        $this->todayDate = date('Ymd');
        $this->identifierBase = substr(
            sprintf('%s-%%s', GenerationUtils::getGuid()),
            0,
            10
        );
    }

    public function test000CleanUp()
    {
        try {
            $results = Schedule::findAll();
            foreach ($results as $schedule) {
                $schedule->delete(true);
            }
        } catch (\Exception $e) {
        }

        try {
            $results = RecurringPaymentMethod::findAll();
            foreach ($results as $paymentMethod) {
                $paymentMethod->delete(true);
            }
        } catch (\Exception $e) {
        }

        try {
            $results = Customer::findAll();
            foreach ($results as $customer) {
                $customer->delete(true);
            }
        } catch (\Exception $e) {
        }
    }

    // CUSTOMER SETUP

    public function test001AddCustomerPerson()
    {
        $customer = new Customer();
        $customer->id = $this->getIdentifier('Person');
        $customer->firstName = 'John';
        $customer->lastName = 'Doe';
        $customer->status = 'Active';
        $customer->email = 'john.doe@example.com';
        $customer->address = new Address();
        $customer->address->streetAddress1 = '123 Main St.';
        $customer->address->city = 'Dallas';
        $customer->address->province = 'TX';
        $customer->address->postalCode = '75024';
        $customer->address->country = 'USA';
        $customer->workPhone = '5551112222';

        $customer = $customer->create();

        $this->assertNotNull($customer);
        $this->assertNotNull($customer->key);
        static::$customerPerson = $customer;

        $found = Customer::find($customer->id);
        $this->assertNotNull($found);
    }

    public function test002AddCustomerBusiness()
    {
        $customer = new Customer();
        $customer->id = $this->getIdentifier('Business');
        $customer->company = 'AcmeCo';
        $customer->status = 'Active';
        $customer->email = 'john.doe@example.com';
        $customer->address = new Address();
        $customer->address->streetAddress1 = '987 Elm St.';
        $customer->address->city = 'Princeton';
        $customer->address->province = 'NJ';
        $customer->address->postalCode = '12345';
        $customer->address->country = 'USA';
        $customer->workPhone = '5551112222';

        $customer = $customer->create();

        $this->assertNotNull($customer);
        $this->assertNotNull($customer->key);
        static::$customerBusiness = $customer;
    }

    // PAYMENT METHOD SETUP

    public function test003AddPaymentCreditVisa()
    {
        if (static::$customerPerson === null) {
            $this->markTestIncomplete();
        }

        $card = new CreditCardData();
        $card->number = '4012002000060016';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $paymentMethod = static::$customerPerson->addPaymentMethod(
            $this->getIdentifier('CreditV'),
            $card
        )->create();

        $this->assertNotNull($paymentMethod);
        $this->assertNotNull($paymentMethod->key);
        static::$paymentMethodVisa = $paymentMethod;
    }

    public function test004AddPaymentCreditMasterCard()
    {
        if (static::$customerPerson === null) {
            $this->markTestIncomplete();
        }

        $card = new CreditCardData();
        $card->number = '5473500000000014';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $paymentMethod = static::$customerPerson->addPaymentMethod(
            $this->getIdentifier('CreditMC'),
            $card
        )->create();

        $this->assertNotNull($paymentMethod);
        $this->assertNotNull($paymentMethod->key);
        static::$paymentMethodMasterCard = $paymentMethod;
    }

    public function test005AddPaymentCheckPpd()
    {
        if (static::$customerPerson === null) {
            $this->markTestIncomplete();
        }

        $check = new ECheck();
        $check->accountType = AccountType::CHECKING;
        $check->checkType = CheckType::PERSONAL;
        $check->secCode = SecCode::PPD;
        $check->routingNumber = '122000030';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '1357902468';
        $check->birthYear = 1989;

        $paymentMethod = static::$customerPerson->addPaymentMethod(
            $this->getIdentifier('CheckPpd'),
            $check
        )->create();

        $this->assertNotNull($paymentMethod);
        $this->assertNotNull($paymentMethod->key);
        static::$paymentMethodCheckPpd = $paymentMethod;
    }

    public function test006AddPaymentCheckCcd()
    {
        if (static::$customerBusiness === null) {
            $this->markTestIncomplete();
        }

        $check = new eCheck();
        $check->accountType = AccountType::CHECKING;
        $check->checkType = CheckType::BUSINESS;
        $check->secCode = SecCode::CCD;
        $check->routingNumber = '122000030';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '1357902468';
        $check->birthYear = 1989;

        $paymentMethod = static::$customerBusiness->addPaymentMethod(
            $this->getIdentifier('CheckCcd'),
            $check
        )->create();

        $this->assertNotNull($paymentMethod);
        $this->assertNotNull($paymentMethod->key);
        static::$paymentMethodCheckCcd = $paymentMethod;
    }

    // PAYMENT SETUP - DECLINED

    /**
     * expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function test007AddPaymentCheckPpd()
    {
        if (static::$customerPerson === null) {
            $this->markTestIncomplete();
        }

        $check = new eCheck();
        $check->accountType = AccountType::CHECKING;
        $check->checkType = CheckType::PERSONAL;
        $check->secCode = SecCode::PPD;
        $check->routingNumber = '122000030';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '1357902468';
        $check->birthYear = 1989;

        static::$customerPerson->addPaymentMethod(
            $this->getIdentifier('CheckPpd'),
            $check
        )->create();
    }

    // Recurring Billing using PayPlan - Managed Schedule

    public function test008AddScheduleCreditVisa()
    {
        if (static::$paymentMethodVisa === null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodVisa->addSchedule(
            $this->getIdentifier('CreditV')
        )
            ->withStatus('Active')
            ->withAmount(30.02)
            ->withCurrency('USD')
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withFrequency(ScheduleFrequency::WEEKLY)
            ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2027-04-01'))
            ->withReprocessingCount(2)
            ->create();

        $this->assertNotNull($schedule);
        $this->assertNotNull($schedule->key);
        static::$scheduleVisa = $schedule;
    }

    public function test009AddScheduleCreditMasterCard() {
        if (static::$paymentMethodMasterCard == null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodMasterCard->addSchedule(
            $this->getIdentifier('CreditMC')
        )
            ->withStatus('Active')
            ->withAmount(30.02)
            ->withCurrency('USD')
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withFrequency(ScheduleFrequency::WEEKLY)
            ->withEndDate(\DateTime::createFromFormat('Y-m-d', '2027-04-01'))
            ->withReprocessingCount(2)
            ->create();
        $this->assertNotNull($schedule);
        $this->assertNotNull($schedule->key);
        static::$scheduleMasterCard = $schedule;
    }

    public function test010AddScheduleCheckPPD() {
        if (static::$paymentMethodCheckPpd == null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodCheckPpd->addSchedule(
            $this->getIdentifier('CheckPPD')
        )
            ->withStatus('Active')
            ->withAmount(30.03)
            ->withCurrency('USD')
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withFrequency(ScheduleFrequency::MONTHLY)
            ->withReprocessingCount(1)
            ->withNumberOfPayments(2)
            ->create();
        $this->assertNotNull($schedule);
        $this->assertNotNull($schedule->key);
        static::$scheduleCheckPpd = $schedule;
    }

    public function test011AddScheduleCheckCCD() {
        if (static::$paymentMethodCheckCcd == null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodCheckCcd->addSchedule(
            $this->getIdentifier('CheckCCD')
        )
            ->withStatus('Active')
            ->withAmount(30.04)
            ->withCurrency('USD')
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withFrequency(ScheduleFrequency::BI_WEEKLY)
            ->withReprocessingCount(1)
            ->create();
        $this->assertNotNull($schedule);
        $this->assertNotNull($schedule->key);
        static::$scheduleCheckCcd = $schedule;
    }

    /**
     * expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function test012AddScheduleCreditVisa() {
        if (static::$paymentMethodVisa == null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodVisa->addSchedule(
            $this->getIdentifier('CreditV')
        )
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withAmount(30.01)
            ->withCurrency('USD')
            ->withFrequency(ScheduleFrequency::WEEKLY)
            ->withReprocessingCount(1)
            ->withStatus('Active')
            ->create();
    }

    /**
     * expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     */
    public function test013AddScheduleCCheckPPD() {
        if (static::$paymentMethodCheckPpd == null) {
            $this->markTestIncomplete();
        }

        $schedule = static::$paymentMethodCheckPpd->addSchedule(
            $this->getIdentifier('CheckPPD')
        )
            ->withStatus('Active')
            ->withAmount(30.03)
            ->withCurrency('USD')
            ->withStartDate(\DateTime::createFromFormat('Y-m-d', '2027-02-01'))
            ->withFrequency(ScheduleFrequency::MONTHLY)
            ->withReprocessingCount(1)
            ->withNumberOfPayments(2)
            ->create();
    }

    // Recurring Billing using PayPlan - Managed Schedule

    public function test014RecurringBillingVisa() {
        if (static::$paymentMethodVisa == null || static::$scheduleVisa == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodVisa->charge(20.01)
            ->withCurrency('USD')
            ->withScheduleId(static::$scheduleVisa->key)
            ->withOneTimePayment(false)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test015RecurringBillingMasterCard() {
        if (true || static::$paymentMethodMasterCard == null || static::$scheduleMasterCard == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodMasterCard->charge(20.02)
            ->withCurrency('USD')
            ->withScheduleId(static::$scheduleVisa->key)
            ->withOneTimePayment(false)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test016RecurringBillingCheckPPD() {
        if (static::$paymentMethodCheckPpd == null || static::$scheduleCheckPpd == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodCheckPpd->charge(20.03)
            ->withCurrency('USD')
            ->withScheduleId(static::$scheduleVisa->key)
            ->withOneTimePayment(false)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test017RecurringBillingCheckCCD() {
        if (static::$paymentMethodCheckCcd == null || static::$scheduleCheckCcd == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodCheckCcd->charge(20.04)
            ->withCurrency('USD')
            ->withScheduleId(static::$scheduleVisa->key)
            ->withOneTimePayment(false)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // One time bill payment

    public function test018RecurringBillingVisa() {
        if (static::$paymentMethodVisa == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodVisa->charge(20.06)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test019RecurringBillingMasterCard() {
        if (static::$paymentMethodMasterCard == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodMasterCard->charge(20.07)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test020RecurringBillingCheckPPD() {
        if (static::$paymentMethodCheckPpd == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodCheckPpd->charge(20.08)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test021RecurringBillingCheckCCD() {
        if (static::$paymentMethodCheckCcd == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodCheckCcd->charge(20.09)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Onetime bill payment - declined

    public function test022RecurringBillingVisa_Decline() {
        if (static::$paymentMethodVisa == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodVisa->charge(10.08)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('51', $response->responseCode);
    }

    public function test023RecurringBillingCheckPPD_Decline() {
        if (true || static::$paymentMethodCheckPpd == null) {
            $this->markTestIncomplete();
        }

        $response = static::$paymentMethodCheckPpd->charge(25.02)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('1', $response->responseCode);
    }
}
