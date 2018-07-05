<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\EcommerceChannel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class MotoTest extends TestCase
{
    const NO_TRANS_IN_BATCH = 'Batch close was rejected because no transactions are associated with the currently open batch.';
    const BATCH_NOT_OPEN = 'Transaction was rejected because it requires a batch to be open.';

    /** @var bool */
    private $useTokens = false;

    /** @var bool */
    private $usePrepaid = false;

    /** @var EcommerceInfo */
    private $ecommerceInfo = null;

    /** @var string */
    public static $visaToken = null;

    /** @var string */
    public static $mastercardToken = null;

    /** @var string */
    public static $discoverToken = null;

    /** @var string */
    public static $amexToken = null;
    
    private $enableCryptoUrl = true;

    /**
     * @return ServicesConfig
     */
    private function config()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MRCQAQBC_VQACBE0rFaZlbDDPieMGP06JDAtjyS7NQ';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }

    protected function setup()
    {
        ServicesContainer::configure($this->config());

        $this->ecommerceInfo = new EcommerceInfo();
        $this->ecommerceInfo->channel = EcommerceChannel::MOTO;
    }

    public function test000CloseBatch()
    {
        try {
            $response = BatchService::closeBatch();
            $this->assertNotNull($response);
            // print 'batch id: ' . $response->id . "\n";
            // print 'sequence number: ' . $response->sequenceNumber . "\n";
        } catch (ApiException $e) {
            if (false === strpos($e->getMessage(), static::BATCH_NOT_OPEN)
                && false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }

    public function test001VerifyVisa()
    {
        $card = new CreditCardData();
        $card->number = '4484958240202792';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->verify()
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test002VerifyMasterCard()
    {
        $card = new CreditCardData();
        $card->number = '5356083898949891';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->verify()
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test003VerifyDiscover()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '6223971100014620';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // StreetAddress verification

    public function test004VerifyAmex()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '345039962663847';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Balance Inquiry (for prepaid card)

    public function test005BalanceInquiryVisa()
    {
        $card = new CreditCardData();
        $card->number = '4664383951958601';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->balanceInquiry()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);
    }

    // CREDIT SALE (for multi-use token only)

    public function test006ChargeVisaToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '4012002000060016';
        $card->expMonth = '12';
        $card->expYear = '2025';

        $response = $card->charge(13.01)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        static::$visaToken = $response->token;
    }

    public function test007ChargeMasterCardToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '5473500000000014';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        $response = $card->charge(13.02)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        static::$mastercardToken = $response->token;
    }

    public function test008ChargeDiscoverToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = new CreditCardData();
        $card->number = '6011000990156527';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        $response = $card->charge(13.03)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        static::$discoverToken = $response->token;
    }

    public function test009ChargeAmexToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '372700699251018';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '1234';

        $response = $card->charge(13.04)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        static::$amexToken = $response->token;
    }

    // CREDIT SALE

    public function test010ChargeVisa()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$visaToken;
        } else {
            $card = TestCards::visaManual();
        }

        $response = $card->charge(17.01)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 35
        $void = $response->void()->execute();

        $this->assertNotNull($void);
        $this->assertEquals('00', $void->responseCode);
    }

    public function test011ChargeMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$mastercardToken;
        } else {
            $card = TestCards::masterCardManual();
        }

        $response = $card->charge(17.02)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test012ChargeDiscover()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$discoverToken;
        } else {
            $card = TestCards::discoverManual();
        }

        $response = $card->charge(17.03)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test013ChargeAmex()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$amexToken;
        } else {
            $card = TestCards::amexManual();
        }

        $response = $card->charge(17.04)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test014ChargeJcb()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '750241234';

        $card = new CreditCardData();
        $card->number = '3566007770007321';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        $response = $card->charge(17.05)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test011bChargeMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardSeries2Manual();

        $response = $card->charge(17.02)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // AUTHORIZATION


    public function test015AuthorizationVisa()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->authorize(17.06)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 15b
        $capture = $response->capture()->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function test016AuthorizationMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '750241234';

        $card = TestCards::masterCardManual();

        $response = $card->authorize(17.07)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 16b
        $capture = $response->capture()->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function test017AuthorizationDiscover()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::discoverManual();

        $response = $card->authorize(17.08)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test016bAuthorizationMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardSeries2Manual();

        $response = $card->authorize(17.02)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // PARTIALLY APPROVED SALE

    public function test018PartialApprovalVisa()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge(130)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals('110.00', $response->authorizedAmount);
    }

    public function test019PartialApprovalDiscover()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::discoverManual();

        $response = $card->charge(145)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals('65.00', $response->authorizedAmount);
    }

    public function test020PartialApprovalMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge(155)
            ->withCurrency('USD')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals('100.00', $response->authorizedAmount);

        // test case 36
        $void = $response->void()->execute();

        $this->assertNotNull($void);
        $this->assertEquals('00', $void->responseCode);
    }
    /// LEVEL II CORPORATE PURCHASE CARD

    public function test021LevelIIResponseB()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '750241234';

        $card = TestCards::visaManual();

        $response = $card->charge(112.34)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('B', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test022LevelIIResponseB()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = TestCards::visaManual();

        $response = $card->charge(112.34)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('B', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test023LevelIIResponseR()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge(123.45)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('R', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test024LevelIIResponseS()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge(134.56)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test025LevelIIResponseS()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();

        $response = $card->charge(111.06)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test026LevelIIResponseS()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();

        $response = $card->charge(111.07)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test027LevelIIResponseS()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();

        $response = $card->charge(111.08)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test028LevelIIResponseS()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();

        $response = $card->charge(111.09)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test029LevelIINoResponse()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::amexManual();

        $response = $card->charge(111.10)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test030LevelIINoResponse()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = TestCards::amexManual();

        $response = $card->charge(111.11)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test031LevelIINoResponse()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::amexManual();

        $response = $card->charge(111.12)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1.00)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function test032LevelIINoResponse()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::amexManual();

        $response = $card->charge(111.13)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();

        $this->assertEquals(true, $cpcResponse != null);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    /// PRIOR / VOICE AUTHORIZATION

    public function test033OfflineSale()
    {
        $card = TestCards::visaManual();

        $response = $card->charge(17.10)
            ->withCurrency('USD')
            ->withModifier(TransactionModifier::OFFLINE)
            ->withOfflineAuthCode('654321')
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test033OfflineAuthorization()
    {
        $card = TestCards::visaManual();

        $response = $card->authorize(17.10)
            ->withCurrency('USD')
            ->withModifier(TransactionModifier::OFFLINE)
            ->withOfflineAuthCode('654321')
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// RETURN

    public function test034OfflineCreditReturn()
    {
        $card = TestCards::masterCardManual();

        $response = $card->refund(15.15)
            ->withCurrency('USD')
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test034bOfflineCreditReturn()
    {
        $card = TestCards::masterCardManual();

        $response = $card->refund(15.16)
            ->withCurrency('USD')
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// ONLINE VOID / REVERSAL

    public function test035VoidTest10()
    {
        // see test 010
    }

    public function test036VoidTest20()
    {
        // see test 020
    }

    /// Time Out Reversal

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException
     */
    public function test036bTimeoutReversal()
    {
        $sale = TestCards::visaManual()->charge(911)
            ->withCurrency('USD')
            ->withClientTransactionId('987321654')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $sale != null);
        $this->assertEquals('91', $sale->responseCode);

        $response = Transaction::fromId(null, PaymentMethodType::CREDIT);
        $response->clientTransactionId = '987321654';

        $response->reverse(911)->execute();
    }

    /// One time bill payment

    public function test010ChargeVisaOneTime()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$visaToken;
        } else {
            $card = TestCards::visaManual();
        }

        $response = $card->charge(13.11)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withOneTimePayment(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        $void = $response->void()->execute();

        $this->assertEquals(true, $void != null);
        $this->assertEquals('00', $void->responseCode);
    }

    public function test011ChargeMasterCardOneTime()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$mastercardToken;
        } else {
            $card = TestCards::masterCardManual();
        }

        $response = $card->charge(13.12)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withOneTimePayment(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test012ChargeDiscoverOneTime()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$discoverToken;
        } else {
            $card = TestCards::discoverManual();
        }

        $response = $card->charge(13.13)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withOneTimePayment(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test013ChargeAmexOneTime()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = null;
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = static::$amexToken;
        } else {
            $card = TestCards::amexManual();
        }

        $response = $card->charge(13.14)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withOneTimePayment(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test014ChargeJcbOneTime()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = new CreditCardData();
        $card->number = '3566007770007321';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        $response = $card->charge(13.15)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withOneTimePayment(true)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// ADVANCED FRAUD SCREENING

    /**
     * TODO: Change code assertions when AFS is enabled on account
     */
    public function test037FraudPreventionSale()
    {
        $card = TestCards::visaManual();

        $response = $card->charge(15000)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        // $this->assertEquals('FR', $response->responseCode);
    }

    /**
     * TODO: Change code assertions when AFS is enabled on account
     */
    public function test038FraudPreventionReturn()
    {
        $card = TestCards::visaManual();

        $response = $card->refund(15000)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        // $this->assertEquals('41', $response->responseCode);
    }

    /// ONE CARD - GSB CARD FUNCTIONS

    /// BALANCE INQUIRY

    public function test037BalanceInquiryGsb()
    {
        if (false === $this->usePrepaid) {
            $this->markTestSkipped('GSB not configured');
        }

        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::gsbManual();

        $response = $card->balanceInquiry()
            ->withAddress($address)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// ADD VALUE

    public function test038AddValueGsb()
    {
        if (false === $this->usePrepaid) {
            $this->markTestSkipped('GSB not configured');
        }

        $card = new CreditTrackData();
        $card->value = '%B6277220572999800^   /                         ^49121010557010000016000000?F;6277220572999800=49121010557010000016?';

        $response = $card->addValue(15.00)
            ->withCurrency('USD')
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// SALE

    public function test039ChargeGsb()
    {
        if (false === $this->usePrepaid) {
            $this->markTestSkipped('GSB not configured');
        }

        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::gsbManual();

        $response = $card->charge(2.05)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        $voidResponse = $response->void()
            ->execute();
        $this->assertEquals(true, $voidResponse != null);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function test040ChargeGsb()
    {
        if (false === $this->usePrepaid) {
            $this->markTestSkipped('GSB not configured');
        }

        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::gsbManual();

        $response = $card->charge(2.10)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// ONLINE VOID / REVERSAL

    public function test041VoidGsb()
    {
        // see test 039
    }

    /// HMS GIFT - REWARDS

    /// ACTIVATE

    public function test042ActivateGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->activate(6.00)
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test043ActivateGift2()
    {

        $card = TestCards::giftCard2Manual();

        $response = $card->activate(7.00)
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// LOAD / ADD VALUE

    public function test044AddValueGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->addValue(8.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test045AddValueGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->addValue(9.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// BALANCE INQUIRY

    public function test046BalanceInquiryGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->balanceInquiry()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('10.00', $response->balanceAmount);
    }

    public function test047BalanceInquiryGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->balanceInquiry()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('10.00', $response->balanceAmount);
    }

    /// REPLACE / TRANSFER

    public function test048ReplaceGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->replaceWith(TestCards::giftCard2Manual())
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('10.00', $response->balanceAmount);
    }

    public function test049ReplaceGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->replaceWith(TestCards::giftCard1Swipe())
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('10.00', $response->balanceAmount);
    }

    /// SALE / REDEEM

    public function test050SaleGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->charge(1.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test051SaleGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->charge(2.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test052SaleGift1Void()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->charge(3.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        $voidResponse = $response->void()
            ->execute();
        $this->assertEquals(true, $voidResponse != null);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function test053SaleGift2Reversal()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->charge(4.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        $reversalResponse = $response->reverse(4.00)
            ->execute();
        $this->assertEquals(true, $reversalResponse != null);
        $this->assertEquals('00', $reversalResponse->responseCode);
    }

    /// VOID

    public function test054VoidGift()
    {
        // see test 052
    }

    /// REVERSAL

    public function test055ReversalGift()
    {
        // see test 053
    }

    public function test056ReversalGift2()
    {
        $card = TestCards::giftCard2Manual();

        $reversalResponse = $card->reverse(2.00)
            ->execute();
        $this->assertEquals(true, $reversalResponse != null);
        $this->assertEquals('00', $reversalResponse->responseCode);
    }

    /// DEACTIVATE

    public function test057DeactivateGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->deactivate()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// RECEIPTS MESSAGING

    public function test058ReceiptsMessaging()
    {
        return;  # print and scan receipt for test 51
    }

    /// REWARD

    /// BALANCE INQUIRY

    public function test059BalanceInquiryRewards1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->balanceInquiry()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->pointsBalanceAmount);
    }

    public function test060BalanceInquiryRewards2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->balanceInquiry()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->pointsBalanceAmount);
    }

    /// ALIAS

    public function test061CreateAliasGift1()
    {
        $response = GiftCard::create('9725550100');

        $this->assertEquals(true, $response != null);
    }

    public function test062CreateAliasGift2()
    {
        $response = GiftCard::create('9725550100');

        $this->assertEquals(true, $response != null);
    }

    public function test063AddAliasGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->addAlias('2145550199')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test064AddAliasGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->addAlias('2145550199')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test065DeleteAliasGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->removeAlias('2145550199')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// SALE / REDEEM

    public function test066RedeemPointsGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->charge(100)
            ->withCurrency('POINTS')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test067RedeemPointsGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->charge(200)
            ->withCurrency('POINTS')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test068RedeemPointsGift2()
    {
        $card = new GiftCard();
        $card->alias = '9725550100';

        $response = $card->charge(300)
            ->withCurrency('POINTS')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// REWARDS

    public function test069RewardsGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->rewards(10)
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test070RewardsGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->rewards(11)
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// REPLACE / TRANSFER

    public function test071ReplaceGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->replaceWith(TestCards::giftCard2Manual())
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test072ReplaceGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->replaceWith(TestCards::giftCard1Swipe())
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// DEACTIVATE

    public function test073DeactivateGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->deactivate()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test074DeactivateGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->deactivate()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// RECEIPTS MESSAGING

    public function test075ReceiptsMessaging()
    {
        return;  # print and scan receipt for test 51
    }

    /// CLOSE BATCH

    public function test999CloseBatch()
    {
        try {
            $response = BatchService::closeBatch();
            $this->assertNotNull($response);
            // printf('batch id: %s', $response->id);
            // printf('sequence number: %s', $response->sequenceNumber);
        } catch (ApiException $e) {
            if (false === strpos($e->getMessage(), static::BATCH_NOT_OPEN)
                || false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }
}
