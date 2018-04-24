<?php

namespace GlobalPayments\Api\Tests\Unit\Builders\AuthorizationBuilder;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
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
        $card->expYear = 2025;
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
        $this->card = $card;

        ServicesContainer::configure($this->getConfig());
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

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MTeSAQAfG1UA9qQDrzl-kz4toXvARyieptFwSKP24w';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }
}
