<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\PaymentMethods\EBTCardData;
use GlobalPayments\Api\PaymentMethods\EBTTrackData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\Entities\Enums\EcommerceChannel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Enums\Secure3dPaymentDataSource;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Services\BatchService;

class TestCards
{
    public static function validCardExpYear()
    {
        return intval(date('Y')) + 1;
    }

    public static function expiredCardExpYear() {
        return 2012;
    }

    public static function asDebit($card, $pinBlock)
    {
        $data = new DebitTrackData();
        $data->value = $card->value;
        $data->encryptionData = $card->encryptionData;
        $data->pinBlock = $pinBlock;
        return $data;
    }

    public static function asEBTTrack($card, $pinBlock)
    {
        $data = new EBTTrackData();
        $data->value = $card->value;
        $data->entryMethod = $card->entryMethod;
        $data->encryptionData = $card->encryptionData;
        $data->pinBlock = $pinBlock;
        return $data;
    }

    public static function asEBTManual($card, $pinBlock)
    {
        $data = new EBTCardData();
        $data->number = $card->number;
        $data->expMonth = $card->expMonth;
        $data->expYear = $card->expYear;
        $data->pinBlock = $pinBlock;
        return $data;
    }

    public static function visaManual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '4012002000060016';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '123';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function visaSwipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function visaSwipeEncrypted($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $encryptionData = new EncryptionData();
        $encryptionData->version = '01';

        $data = new CreditTrackData();
        $data->value = '<E1050711%B4012001000000016^VI TEST CREDIT^251200000000000000000000?|LO04K0WFOmdkDz0um+GwUkILL8ZZOP6Zc4rCpZ9+kg2T3JBT4AEOilWTI|+++++++Dbbn04ekG|11;4012001000000016=25120000000000000000?|1u2F/aEhbdoPixyAPGyIDv3gBfF|+++++++Dbbn04ekG|00|||/wECAQECAoFGAgEH2wYcShV78RZwb3NAc2VjdXJlZXhjaGFuZ2UubmV0PX50qfj4dt0lu9oFBESQQNkpoxEVpCW3ZKmoIV3T93zphPS3XKP4+DiVlM8VIOOmAuRrpzxNi0TN/DWXWSjUC8m/PI2dACGdl/hVJ/imfqIs68wYDnp8j0ZfgvM26MlnDbTVRrSx68Nzj2QAgpBCHcaBb/FZm9T7pfMr2Mlh2YcAt6gGG1i2bJgiEJn8IiSDX5M2ybzqRT86PCbKle/XCTwFFe1X|>;';
        $data->entryMethod = $entryMethod;
        $data->encryptionData = $encryptionData;
        return $data;
    }

    public static function masterCardManual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '5473500000000014';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '123';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function masterCardSeries2Manual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '2223000010005780';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '123';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function masterCardSwipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B5473500000000014^MC TEST CARD^251210199998888777766665555444433332?;5473500000000014=25121019999888877776?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function masterCard24Swipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B2223000010005780^TEST CARD/EMV BIN-2^19121010000000009210?;2223000010005780=19121010000000009210?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function masterCard25Swipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B2223000010005798^TEST CARD/EMV BIN-2^19121010000000003840?;2223000010005798=19121010000000003840?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function masterCardSwipeEncrypted($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $encryptionData = new EncryptionData();
        $encryptionData->version = '01';

        $data = new CreditTrackData();
        $data->value = '<E1052711%B5473501000000014^MC TEST CARD^251200000000000000000000000000000000?|GVEY/MKaKXuqqjKRRueIdCHPPoj1gMccgNOtHC41ymz7bIvyJJVdD3LW8BbwvwoenI+|+++++++C4cI2zjMp|11;5473501000000014=25120000000000000000?|8XqYkQGMdGeiIsgM0pzdCbEGUDP|+++++++C4cI2zjMp|00|||/wECAQECAoFGAgEH2wYcShV78RZwb3NAc2VjdXJlZXhjaGFuZ2UubmV0PX50qfj4dt0lu9oFBESQQNkpoxEVpCW3ZKmoIV3T93zphPS3XKP4+DiVlM8VIOOmAuRrpzxNi0TN/DWXWSjUC8m/PI2dACGdl/hVJ/imfqIs68wYDnp8j0ZfgvM26MlnDbTVRrSx68Nzj2QAgpBCHcaBb/FZm9T7pfMr2Mlh2YcAt6gGG1i2bJgiEJn8IiSDX5M2ybzqRT86PCbKle/XCTwFFe1X|>';
        $data->entryMethod = $entryMethod;
        $data->encryptionData = $encryptionData;
        return $data;
    }

    public static function discoverManual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '6011000990156527';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '123';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function discoverSwipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B6011000990156527^DIS TEST CARD^25121011000062111401?;6011000990156527=25121011000062111401?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function discoverSwipeEncrypted($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $encryptionData = new EncryptionData();
        $encryptionData->version = '01';

        $data = new CreditTrackData();
        $data->value = '<E1049711%B6011000000006527^DIS TEST CARD^25120000000000000000?|nqtDvLuS4VHJd1FymxBxihO5g/ZDqlHyTf8fQpjBwkk95cc6PG9V|+++++++C+LdWXLpP|11;6011000000006527=25120000000000000000?|8VfZvczP6iBqRis2XFypmktaipa|+++++++C+LdWXLpP|00|||/wECAQECAoFGAgEH2wYcShV78RZwb3NAc2VjdXJlZXhjaGFuZ2UubmV0PX50qfj4dt0lu9oFBESQQNkpoxEVpCW3ZKmoIV3T93zphPS3XKP4+DiVlM8VIOOmAuRrpzxNi0TN/DWXWSjUC8m/PI2dACGdl/hVJ/imfqIs68wYDnp8j0ZfgvM26MlnDbTVRrSx68Nzj2QAgpBCHcaBb/FZm9T7pfMr2Mlh2YcAt6gGG1i2bJgiEJn8IiSDX5M2ybzqRT86PCbKle/XCTwFFe1X|>';
        $data->entryMethod = $entryMethod;
        $data->encryptionData = $encryptionData;
        return $data;
    }

    public static function amexManual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '372700699251018';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '1234';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function amexSwipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B3727 006992 51018^AMEX TEST CARD^2512990502700?;372700699251018=2512990502700?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function jcbManual($cardPresent = false, $readerPresent = false)
    {
        $data = new CreditCardData();
        $data->number = '3566007770007321';
        $data->expMonth = 12;
        $data->expYear = self::validCardExpYear();
        $data->cvn = '123';
        $data->cardPresent = $cardPresent;
        $data->readerPresent = $readerPresent;
        return $data;
    }

    public static function jcbSwipe($entryMethod = null)
    {
        if ($entryMethod === null) {
            $entryMethod = EntryMethod::SWIPE;
        }

        $data = new CreditTrackData();
        $data->value = '%B3566007770007321^JCB TEST CARD^2512101100000000000000000064300000?;3566007770007321=25121011000000076435?';
        $data->entryMethod = $entryMethod;
        return $data;
    }

    public static function giftCard1Swipe()
    {
        $data = new GiftCard();
        $data->trackData = '%B5022440000000000098^^391200081613?;5022440000000000098=391200081613?';
        return $data;
    }

    public static function giftCard2Manual()
    {
        $data = new GiftCard();
        $data->number = '5022440000000000007';
        return $data;
    }

    public static function gsbManual()
    {
        $data = new CreditCardData();
        $data->number = '6277220572999800';
        $data->expMonth = '12';
        $data->expYear = self::validCardExpYear();
        return $data;
    }
}



class EcommerceTest extends TestCase
{
    const NO_TRANS_IN_BATCH = 'Batch close was rejected because no transactions are associated with the currently open batch.';
    const BATCH_NOT_OPEN = 'Transaction was rejected because it requires a batch to be open.';

    /** @var bool */
    private $useTokens = true;

    /** @var bool */
    private $usePrepaid = false;

    /** @var string */
    private $publicKey = '';

    /** @var EcommerceInfo */
    private $ecommerceInfo = null;

    /** @var string|null */
    public static $visaToken = null;

    /** @var string|null */
    public static $mastercardToken = null;

    /** @var string|null */
    public static $discoverToken = null;

    /** @var string|null */
    public static $amexToken = null;

    private $enableCryptoUrl = true;

    private function config()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }

    protected function setup() : void
    {
        ServicesContainer::configureService($this->config());
        $this->publicKey = 'pkapi_cert_jKc1FtuyAydZhZfbB3';

        $this->ecommerceInfo = new EcommerceInfo();
        $this->ecommerceInfo->channel = EcommerceChannel::ECOM;
    }

    public function test000CloseBatch()
    {
        try {
            $response = BatchService::closeBatch();
            $this->assertNotNull($response);
        } catch (ApiException $e) {
            if (false === strpos($e->getMessage(), static::BATCH_NOT_OPEN)
                && false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }

    /// CARD VERIFY

    /// Account Verification

    public function test001VerifyVisa()
    {
        $card = TestCards::visaManual();

        $response = $card->verify()
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test002VerifyMasterCard()
    {
        $card = TestCards::masterCardManual();
        $response = $card->verify()
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test003VerifyDiscover()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::discoverManual();

        $response = $card->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// Address Verification

    public function test004VerifyAmex()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::amexManual();

        $response = $card->verify()
            ->withAddress($address)
            ->withRequestMultiUseToken($this->useTokens)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// Balance Inquiry (for Prepaid Card)

    public function test005BalanceInquiryVisa()
    {
        if (false === $this->usePrepaid) {
            $this->markTestSkipped('GSB not configured');
        }

        $card = TestCards::visaManual();

        $response = $card->balanceInquiry()
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// CREDIT SALE (For Multi-Use Token Only)

    public function test006ChargeVisaToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(13.01)
            ->withInvoiceNumber('123456')
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(true, $response->token != null);
        self::$visaToken = $response->token;
    }

    public function test007ChargeMasterCardToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(13.02)
            ->withInvoiceNumber('123456')
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(true, $response->token != null);
        self::$mastercardToken = $response->token;
    }

    public function test008ChargeDiscoverToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = TestCards::discoverManual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(13.03)
            ->withInvoiceNumber('123456')
            ->withAddress($address)
            ->withRequestMultiUseToken(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(true, $response->token != null);
        self::$discoverToken = $response->token;
    }

    public function test009ChargeAmexToken()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(13.04)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withRequestMultiUseToken(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(true, $response->token != null);
        self::$amexToken = $response->token;
    }

    /// CREDIT SALE

    public function test010ChargeVisa()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = self::$visaToken;
        }

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.01)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        $voidResponse = $response->void()
            ->execute();
        $this->assertEquals(true, $voidResponse != null);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function test011ChargeMastercard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual();
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = self::$mastercardToken;
        }

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.02)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test012ChargeDiscover()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '750241234';

        $card = TestCards::discoverManual();
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = self::$discoverToken;
        }

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.03)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test013ChargeAmex()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::amexManual();
        if ($this->useTokens) {
            $card = new CreditCardData();
            $card->token = self::$amexToken;
        }

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.04)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test014ChargeJcb()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';

        $card = TestCards::jcbManual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.04)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test011bChargeMasterCard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::masterCardSeries2Manual();

        $response = $card->charge()
            ->withCurrency('USD')
            ->withAmount(17.02)
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    /// AUTHORIZATION

    public function test015AuthorizationVisa()
    {
        # Test 015a Authorization
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';


        $card = TestCards::visaManual();

        $response = $card->authorize(17.06)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        # test 015b Capture/AddToBatch
        $capture = $response->capture()
            ->execute();
        $this->assertEquals(true, $capture != null);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function test016AuthorizationMastercard()
    {
        # Test 016a Authorization
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '750241234';

        $card = TestCards::masterCardManual();

        $response = $card->authorize(17.07)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        # test 016b Capture/AddToBatch
        $capture = $response->capture()
            ->execute();
        $this->assertEquals(true, $capture != null);
        $this->assertEquals('00', $capture->responseCode);
    }

    public function test017AuthorizationDiscover()
    {
        # Test 017a Authorization
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::discoverManual();

        $response = $card->authorize(17.08)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);

        # test 017b Capture/AddToBatch
        # do not capture
    }

    /// PARTIALLY - APPROVED SALE

    public function test018PartialApprovalVisa()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::visaManual();

        $response = $card->charge(130)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(true, $response->authorizedAmount != null);
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
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(true, $response->authorizedAmount != null);
        $this->assertEquals('65.00', $response->authorizedAmount);
    }

    public function test020PartialApprovalMastercard()
    {
        $address = new Address();
        $address->streetAddress1 = '6860';
        $address->postalCode = '75024';

        $card = TestCards::discoverManual();

        $response = $card->charge(155)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowPartialAuth(true)
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $response != null);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(true, $response->authorizedAmount != null);
        $this->assertEquals('100.00', $response->authorizedAmount);

        $voidResponse = $response->void()
            ->execute();
        $this->assertEquals(true, $voidResponse != null);
        $this->assertEquals('00', $voidResponse->responseCode);
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
            ->withInvoiceNumber('123456')
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
    public function test036bTimeoutReversal()
    {
        $sale = TestCards::visaManual()->charge(911)
            ->withCurrency('USD')
            ->withInvoiceNumber('123456')
            ->withClientTransactionId('987321654')
            ->withEcommerceInfo($this->ecommerceInfo)
            ->execute();

        $this->assertEquals(true, $sale != null);
        $this->assertEquals('91', $sale->responseCode);

        $response = Transaction::fromId(null, PaymentMethodType::CREDIT);
        $response->clientTransactionId = '987321654';

        $this->expectException(GatewayException::class);
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
        $card->expYear = TestCards::validCardExpYear();
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
            ->withCurrency('USD')
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test043ActivateGift2()
    {

        $card = TestCards::giftCard2Manual();

        $response = $card->activate(7.00)
            ->withCurrency('USD')
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
        $this->assertTrue($response->pointsBalanceAmount > 0);
    }

    public function test060BalanceInquiryRewards2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->balanceInquiry()
            ->execute();
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
        $this->assertTrue($response->pointsBalanceAmount > 0);
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
                && false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }
    
    public function test100ChargeVisaEcommerceInfo()
    {
        $address = new Address();
        $address->streetAddress1 = '6860 Dallas Pkwy';
        $address->postalCode = '75024';
        
        $secureEcom = new ThreeDSecure();
        $secureEcom->cavv = 'AAACBllleHchZTBWIGV4AAAAAAA=';
        $secureEcom->xid = 'crqAeMwkEL9r4POdxpByWJ1/wYg=';
        $secureEcom->eci = '5';
        $secureEcom->paymentDataSource = Secure3dPaymentDataSource::VISA_3DSECURE;
        $secureEcom->paymentDataType = '3DSecure';
        
        $card = TestCards::visaManual();
        $card->threeDSecure = $secureEcom;
        
        $response = $card->charge()
        ->withCurrency('USD')
        ->withAmount(13.01)
        ->withAddress($address)
        // ->withEcommerceInfo($this->ecommerceInfo)
        ->withInvoiceNumber('12345')
        ->withAllowDuplicates(true)
        ->execute();
        
        $this->assertEquals(true, $response != null);
        $this->assertEquals('00', $response->responseCode);
    }
}
