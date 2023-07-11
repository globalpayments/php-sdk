<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransactionApiConnector;

use GlobalPayments\Api\Entities\{Address, Customer, PhoneNumber, Transaction};
use GlobalPayments\Api\Entities\Enums\{
    AddressType,
    EcommerceIndicator,
    PaymentMethodType,
    PhoneNumberType,
    TransactionLanguage
};
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class TransactionApiCreditReturnTest extends TestCase
{
    private CreditCardData $card;
    private $currency = "840";
    private Address $address;
    private Address $addressCa;


    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->cvn = "131";
        $this->card->expMonth = date('m');
        $this->card->expYear = substr(date('Y', strtotime('+1 year')), -2);
        $this->card->cardHolderName = "James Mason";

        $this->address = new Address();
        $this->address->streetAddress1 = "2600 NW";
        $this->address->streetAddress2 = "23th Street";
        $this->address->city = "Lindon";
        $this->address->state = "Utah";
        $this->address->country = "USA";
        $this->address->postalCode = "84042";

        $this->addressCa = new Address();
        $this->addressCa->streetAddress1 = "Suite 101-290";
        $this->addressCa->streetAddress2 = "Suite 109";
        $this->addressCa->city = "Diamond Bar";
        $this->addressCa->state = "CA";
        $this->addressCa->country = "Canada";
        $this->addressCa->postalCode = "91765";
      }

    public function setUpConfig($country = "US")
    {
        $config = new TransactionApiConfig();
        $config->accountCredential = '800000052925:80039923:eWcWNJhfxiJ7QyEHSHndWk4VHKbSmSue';
        $config->apiSecret         = 'lucQKkwz3W3RGzABkSWUVZj1Mb0Yx3E9chAA8ESUVAv'; #gitleaks:allow
        $config->apiKey            = 'qeG6EWZOiAwk4jsiHzsh2BN8VkN2rdAs'; #gitleaks:allow
        $config->apiVersion        = '2021-04-08';
        $config->apiPartnerName    = 'mobile_sdk';
        $config->country           = $country;
        $config->requestLogger     = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function test01USCreditReturn()
    {
        $transData = $this->getTransactionData('US');

        $response = $this->card->refund('23.00')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test02CACreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->refund('2.00')
            ->withCurrency('124')
            ->withInvoiceNumber('123456')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test03CACreditSaleCreditReturn()
    {
        $config = $this->setUpConfig('CA');
        ServicesContainer::configureService($config);

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->charge('8.00')
            ->withCurrency('124')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $creditSaleId = $response->transactionId;

        $config->country = 'US';

        $transaction = Transaction::fromId(
            $creditSaleId,
            null,
            PaymentMethodType::CREDIT
        );

        $response = $transaction->refund('3.00')
            ->withCurrency('840')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test04USCreditSaleCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));
        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('15.00')
            ->withCurrency('840')
            ->withInvoiceNumber('123456')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $creditSaleId = $response->transactionId;

        $transaction = Transaction::fromId(
            $creditSaleId,
            null,
            PaymentMethodType::CREDIT
        );

        $response = $transaction->refund('10.00')
            ->withCurrency('840')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test05USReferenceIdCreditSaleCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));

        $transData = $this->getTransactionData('US');

        $response = $this->card->charge('13.00')
            ->withCurrency('840')
            ->withInvoiceNumber('2256667')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $referenceId = $response->referenceNumber;

        $transaction = Transaction::fromClientTransactionId($referenceId);

        $response = $transaction->refund('13.00')
            ->withCurrency('840')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
        $this->assertEquals($referenceId, $response->referenceNumber);
    }

    public function test06CAReferenceIdCreditSaleCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->charge('17.00')
            ->withCurrency('124')
            ->withInvoiceNumber('123456')
            ->withAddress($this->address, AddressType::BILLING)
            ->withAddress($this->address, AddressType::SHIPPING)
            ->withTransactionData($transData)
            ->execute();

        $referenceId = $response->referenceNumber;

        $transaction = Transaction::fromClientTransactionId($referenceId);

        $response = $transaction->refund('17.00')
            ->withCurrency('124')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
        $this->assertEquals($referenceId, $response->referenceNumber);
    }

    public function test07CAWithTokenCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('CA'));

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('124')
            ->withRequestMultiUseToken(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->token);

        $this->card->token = $transaction->token;

        $transData = $this->getTransactionData(
            'CA',
            CountryUtils::getNumericCodeByCountry('CA'),
            TransactionLanguage::EN_CA
        );

        $response = $this->card->refund('2.00')
            ->withCurrency('124')
            ->withInvoiceNumber('123456')
            ->withAddress($this->addressCa, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    public function test08USWithTokenCreditReturn()
    {
        ServicesContainer::configureService($this->setUpConfig('US'));
        $transData = $this->getTransactionData('US');
        $transData->addressVerificationService = false;
        $transData->generateReceipt = false;
        $transData->partialApproval = false;

        $transaction = $this->card->authorize('0.0')
            ->withCurrency('840')
            ->withRequestMultiUseToken(true)
            ->withTransactionData($transData)
            ->execute();

        $this->assertNotNull($transaction->token);

        $this->card->token = $transaction->token;

        $transData = $this->getTransactionData('US');

        $response = $this->card->refund('7.00')
            ->withCurrency('840')
            ->withInvoiceNumber('239087')
            ->withAddress($this->address, AddressType::BILLING)
            ->withTransactionData($transData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("approved", $response->responseCode);
    }

    private function getTransactionData($region, $countryCode = null, $lang = null)
    {
        $transData = new TransactionApiData();
        $transData->countryCode = isset($countryCode) ? $countryCode : CountryUtils::getNumericCodeByCountry('US');
        $transData->ecommerceIndicator = EcommerceIndicator::ECOMMERCE_INDICATOR_2;
        $transData->language = isset($lang) ? $lang : TransactionLanguage::EN_US;
        $transData->softDescriptor = "soft";
        $transData->region = $region;
        return $transData;
    }
}
