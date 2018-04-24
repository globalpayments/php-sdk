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
use GlobalPayments\Api\Entities\Exceptions\ApiException;
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

    // public function test000CleanUp()
    // {
    //     try {
    //         $results = Schedule::findAll();
    //         foreach ($results as $schedule) {
    //             $schedule->delete(true);
    //         }
    //     } catch (Exception $e) {
    //     }

    //     try {
    //         $results = RecurringPaymentMethod::findAll();
    //         foreach ($results as $paymentMethod) {
    //             $paymentMethod->delete(true);
    //         }
    //     } catch (Exception $e) {
    //     }

    //     try {
    //         $results = Customer::findAll();
    //         foreach ($results as $customer) {
    //             $customer->delete(true);
    //         }
    //     } catch (Exception $e) {
    //     }
    // }

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
        static::$customerPerson = $customer;
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
        $check->routingNumber = '490000018';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '24413815';
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
        $check->routingNumber = '490000018';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '24413815';
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

    public function test007AddPaymentCheckPpd()
    {
        if (static::$customerPerson === null) {
            $this->markTestIncomplete();
        }

        $check = new eCheck();
        $check->accountType = AccountType::CHECKING;
        $check->checkType = CheckType::PERSONAL;
        $check->secCode = SecCode::PPD;
        $check->routingNumber = '490000018';
        $check->driversLicenseNumber = '7418529630';
        $check->driversLicenseState = 'TX';
        $check->accountNumber = '24413815';
        $check->birthYear = 1989;

        static::$customerPerson->addPaymentMethod(
            $this->getIdentifier('CheckPpd'),
            $check
        )->create();
    }
}
