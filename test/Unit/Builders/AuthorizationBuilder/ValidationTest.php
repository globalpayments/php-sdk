<?php

namespace GlobalPayments\Api\Tests\Unit\Builders\AuthorizationBuilder;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
Use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    protected $card;
    private $enableCryptoUrl = true;

    public function setup()
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
        $this->card = $card;

        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '1357902468';
        $this->eCheck->routingNumber = '122000030';
        $this->eCheck->checkType = CheckType::PERSONAL;
        $this->eCheck->secCode = SecCode::PPD;
        $this->eCheck->accountType = AccountType::CHECKING;
        $this->eCheck->entryMode = EntryMethod::MANUAL;
        $this->eCheck->checkHolderName = 'John Doe';
        $this->eCheck->driversLicenseNumber = '09876543210';
        $this->eCheck->driversLicenseState = 'TX';
        $this->eCheck->phoneNumber = '';
        $this->eCheck->birthYear = '1997';
        $this->eCheck->ssnLast4 = '4321';

        $this->address = new Address();
        $this->address->streetAddress1 = '123 Main St.';
        $this->address->city = 'Downtown';
        $this->address->state = 'NJ';
        $this->address->postalCode = '';

        ServicesContainer::configureService($this->getConfig());
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type.
     */
    public function testCreditAuthNoAmount()
    {
        $this->card->authorize()
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage currency cannot be null
     */
    public function testCreditAuthNoCurrency()
    {
        $this->card->authorize(14)
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage paymentMethod cannot be null
     */
    public function testCreditAuthNoPaymentMethod()
    {
        $this->card->authorize(14)
            ->withCurrency('USD')
            ->withPaymentMethod(null)
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null
     */
    public function testCreditSaleNoAmount()
    {
        $this->card->charge()
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage currency cannot be null
     */
    public function testCreditSaleNoCurrency()
    {
        $this->card->charge(14)
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage paymentMethod cannot be null
     */
    public function testCreditSaleNoPaymentMethod()
    {
        $this->card->charge(14)
            ->withCurrency('USD')
            ->withPaymentMethod(null)
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exaceptions\ApiException
     * @expectedExceptionMessage phone number can not be empty or invalid
     */
    public function testCreditSalePhoneNumberValidateMethod()
    {
        $this->eCheck->phoneNumber = '123456789012345678901';
        $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\ApiException
     * @expectedExceptionMessage zip code can not be empty or invalid
     */
    public function testCreditSaleZipValidateMethod()
    {
        $this->address->postalCode = '0123456789';
        $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTeSAQAfG1UA9qQDrzl-kz4toXvARyieptFwSKP24w';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }
}
