<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\InquiryType;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class RetailTest extends TestCase
{
    const NO_TRANS_IN_BATCH = 'Batch close was rejected because no transactions are associated with the currently open batch.';
    const BATCH_NOT_OPEN = 'Transaction was rejected because it requires a batch to be open.';

    private static $useTokens = false;

    private static $visatoken;
    private static $mastercardtoken;
    private static $discovertoken;
    private static $amextoken;
    private $enableCryptoUrl = true;

    public function __construct()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MaePAQBr-1QAqjfckFC8FTbRTT120bVQUlfVOjgCBw';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        ServicesContainer::configureService($config);
    }

    public function testRetail000CloseBatch()
    {
        try {
            $response = BatchService::closeBatch();
            $this->assertNotNull($response);
            // error_log(sprintf('Batch ID: %s', $response->Id));
            // error_log(sprintf('Sequence Number: %s', $response->sequenceNumber));
        } catch (ApiException $e) {
            if (false === strpos($e->getMessage(), static::BATCH_NOT_OPEN)
                && false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }

    /*
        CREDIT CARD FUNCTIONS
        CARD VERIFY
        ACCOUNT VERIFICATION
      */

    public function testRetail001CardVerifyVisa()
    {
        $visaenc = TestCards::visaSwipeEncrypted();

        $response = $visaenc->verify()
            ->withAllowDuplicates(true)
            ->withRequestMultiUseToken(static::$useTokens)
            ->execute();
        $this->assertNotNull($response, '$response is null');
        $this->assertEquals('00', $response->responseCode, $response->responseMessage);

        if (static::$useTokens) {
            $this->assertNotNull($response->token, 'token is null');

            $token = new CreditCardData();
            $token->token = $response->token;

            $saleResponse = $token->charge(15.01)
                ->withAllowDuplicates(true)
                ->execute();
            $this->assertNotNull($saleResponse);
            $this->assertEquals('00', $saleResponse->responseCode);
        }
    }

    public function testRetail002CardVerifyMastercardSwipe()
    {
        $cardenc = TestCards::masterCardSwipeEncrypted();

        $response = $cardenc->verify()
            ->withAllowDuplicates(true)
            ->withRequestMultiUseToken(static::$useTokens)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        if (static::$useTokens) {
            $this->assertNotNull($response->token);

            $token = new CreditCardData();
            $token->token = $response->token;

            $saleResponse = $token->charge(15.02)
                ->withAllowDuplicates(true)
                ->execute();
            $this->assertNotNull($saleResponse);
            $this->assertEquals('00', $saleResponse->responseCode);
        }
    }

    public function testRetail003CardVerifyDiscover()
    {
        $discoverenc = TestCards::discoverSwipeEncrypted();

        $response = $discoverenc->verify()
            ->withAllowDuplicates(true)
            ->withRequestMultiUseToken(static::$useTokens)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        if (static::$useTokens) {
            $this->assertNotNull($response->token);

            $token = new CreditCardData();
            $token->token = $response->token;

            $saleResponse = $token->charge(15.03)
                ->withAllowDuplicates(true)
                ->execute();
            $this->assertNotNull($saleResponse);
            $this->assertEquals('00', $saleResponse->responseCode);
        }
    }

    // Address Verification

    public function testRetail004CardVerifyAmex()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $manualamex = TestCards::amexManual(false, true);

        $response = $manualamex->verify()
            ->withAllowDuplicates(true)
            ->withAddress($address)
            ->withRequestMultiUseToken(static::$useTokens)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        if (static::$useTokens) {
            $this->assertNotNull($response->token);

            $token = new CreditCardData();
            $token->token = $response->token;

            $saleResponse = $token->charge(15.04)
                ->withAllowDuplicates(true)
                ->execute();
            $this->assertNotNull($saleResponse);
            $this->assertEquals('00', $saleResponse->responseCode);
        }
    }

    // Balance Inquiry (for Prepaid)

    public function testRetail005BalanceInquiryVisa()
    {
        $visaenc = TestCards::visaSwipeEncrypted();

        $response = $visaenc->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // CREDIT SALE (For multi-use token only)

    public function testRetail006ChargeVisaSwipeToken()
    {
        $card = TestCards::visaSwipe();
        $response = $card->charge(15.01)
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        static::$visatoken = $response->token;
    }

    public function testRetail007ChargeMastercardSwipeToken()
    {
        $card = TestCards::masterCardSwipe();
        $response = $card->charge(15.02)
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        static::$mastercardtoken = $response->token;
    }

    public function testRetail008ChargeDiscoverSwipeToken()
    {
        $card = TestCards::discoverSwipe();
        $response = $card->charge(15.03)
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        static::$discovertoken = $response->token;
    }

    public function testRetail009ChargeAmexSwipeToken()
    {
        $card = TestCards::amexSwipe();
        $response = $card->charge(15.04)
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        static::$amextoken = $response->token;
    }

    /*
        CREDIT SALE
        SWIPED
      */

    public function testRetail010ChargeVisaSwipe()
    {
        $card = TestCards::visaSwipe();
        $response = $card->charge(15.01)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test 59
        $reverse = $response->reverse(15.01)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reverse);
        $this->assertEquals('00', $reverse->responseCode);
    }

    public function testRetail011ChargeMastercardSwipe()
    {
        $card = TestCards::masterCardSwipe();
        $response = $card->charge(15.02)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail012ChargeDiscoverSwipe()
    {
        $card = TestCards::discoverSwipe();
        $response = $card->charge(15.03)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail013ChargeAmexSwipe()
    {
        $card = TestCards::amexSwipe();
        $response = $card->charge(15.04)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail014ChargeJcbSwipe()
    {
        $card = TestCards::JcbSwipe();
        $response = $card->charge(15.05)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 58
        $refund = $response->refund(15.05)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($refund);
        $this->assertEquals('00', $refund->responseCode);
    }

    public function testRetail014aChargeRetailMastercard24()
    {
        $card = TestCards::masterCard24Swipe();
        $response = $card->charge(15.34)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail014bChargeRetailMastercard25()
    {
        $card = TestCards::masterCard25Swipe();
        $response = $card->charge(15.34)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail015ChargeVisaSwipe()
    {
        $card = TestCards::visaSwipe();
        $response = $card->charge(15.06)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 63
        $reversal = $response->reverse(15.06)
            ->withAllowDuplicates(true)
            ->withAuthAmount(5.06)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Manually Entered - Card Present

    public function testRetail016ChargeVisaManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $manualcard = TestCards::visaManual(true, true);
        $response = $manualcard->charge(16.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail017ChargeMasterCardManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $manualcard = TestCards::masterCardManual(true, true);
        $response = $manualcard->charge(16.02)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 60
        $reverse = $response->reverse(16.02)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reverse);
        $this->assertEquals('00', $reverse->responseCode);
    }

    public function testRetail018ChargeDiscoverManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';

        $manualcard = TestCards::discoverManual(true, true);
        $response = $manualcard->charge(16.03)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail019ChargeAmexManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860';

        $manualcard = TestCards::amexManual(true, true);
        $response = $manualcard->charge(16.04)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail020ChargeJcbManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $manualcard = TestCards::JcbManual(true, true);
        $response = $manualcard->charge(16.05)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail021ChargeDiscoverManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $manualcard = TestCards::discoverManual(true, true);
        $response = $manualcard->charge(16.07)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 64
        $reversal = $response->reverse(16.07)
            ->withAllowDuplicates(true)
            ->withAuthAmount(6.07)
            ->execute();
        $this->assertNotNull($reversal);
        $this->assertEquals('00', $reversal->responseCode);
    }

    // Manually Entered - Card Not Present

    public function testRetail022ChargeVisaManualCardNotPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $manualcard = null;
        if (static::$useTokens) {
            $manualcard = new CreditCardData();
            $manualcard->token = static::$visatoken;
        } else {
            $manualcard = TestCards::visaManual(false, true);
        }

        $response = $manualcard->charge(17.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail023ChargeMasterCardManualCardNotPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $manualcard = null;
        if (static::$useTokens) {
            $manualcard = new CreditCardData();
            $manualcard->token = static::$mastercardtoken;
        } else {
            $manualcard = TestCards::masterCardManual(false, true);
        }

        $response = $manualcard->charge(17.02)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 61
        $reversal = $response->reverse(17.02)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reversal);
        $this->assertEquals('00', $reversal->responseCode);
    }

    public function testRetail024ChargeDiscoverManualCardNotPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';

        $manualcard = null;
        if (static::$useTokens) {
            $manualcard = new CreditCardData();
            $manualcard->token = static::$discovertoken;
        } else {
            $manualcard = TestCards::discoverManual(false, true);
        }

        $response = $manualcard->charge(17.03)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail025ChargeAmexManualCardNotPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860';

        $manualcard = null;
        if (static::$useTokens) {
            $manualcard = new CreditCardData();
            $manualcard->token = static::$amextoken;
        } else {
            $manualcard = TestCards::amexManual(false, true);
        }

        $response = $manualcard->charge(17.04)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail026ChargeJcbManualCardNotPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $manualcard = TestCards::JcbManual(false, true);
        $response = $manualcard->charge(17.05)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Contactless

    public function testRetail027ChargeVisaContactless()
    {
        $card = TestCards::visaSwipe(EntryMethod::PROXIMITY);
        $response = $card->charge(18.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail028ChargeMastercardContactless()
    {
        $card = TestCards::masterCardSwipe(EntryMethod::PROXIMITY);

        $response = $card->charge(18.02)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail029ChargeDiscoverContactless()
    {
        $card = TestCards::discoverSwipe(EntryMethod::PROXIMITY);

        $response = $card->charge(18.03)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail030ChargeAmexContactless()
    {
        $card = TestCards::amexSwipe(EntryMethod::PROXIMITY);

        $response = $card->charge(18.04)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // AUTHORIZATION

    public function testRetail031AuthorizeVisaSwipe()
    {
        $card = TestCards::visaSwipe();

        // 031a authorize
        $response = $card->authorize(15.08)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 031b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail032AuthorizeVisaSwipeAdditionalAuth()
    {
        $card = TestCards::visaSwipe();

        // 032a authorize
        $response = $card->authorize(15.09)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 032b Additional Auth (restaurant only)

        // 032c Add to batch
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail033AuthorizeMasterCardSwipe()
    {
        $card = TestCards::masterCardSwipe();

        // 033a authorize
        $response = $card->authorize(15.10)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 033b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail033aAuthorizeDiscoverSwipe()
    {
        $card = TestCards::discoverSwipe();

        $response = $card->authorize(15.10)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // AUTHORIZATION - Manually Entered, Card Present

    public function testRetail034AuthorizeVisaManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $card = TestCards::visaManual(true, true);

        // 034a authorize
        $response = $card->authorize(16.08)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 034b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail035AuthorizeVisaManualCardPresentAdditionalAuth()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $card = TestCards::visaManual(true, true);

        // 035a authorize
        $response = $card->authorize(16.09)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 035b Additional Auth (restaurant only)

        // 035c Add to batch
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail036AuthorizeMasterCardManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $card = TestCards::masterCardManual(true, true);

        // 036a authorize
        $response = $card->authorize(16.10)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 036b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail036aAuthorizeDiscoverManualCardPresent()
    {
        $address = new Address();
        $address->postalCode = '750241234';

        $card = TestCards::discoverManual(true, true);
        $response = $card->authorize(16.10)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // AUTHORIZATION - Manually Entered, Card Not Present

    public function testRetail037AuthorizeVisaManual()
    {
        $address = new Address();
        $address->postalCode = '750241234';
        $address->streetAddress1 = '6860 Dallas Pkwy';

        $card = TestCards::visaManual(false, true);

        // 034a authorize
        $response = $card->authorize(17.08)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 034b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail038AuthorizeMasterCardManual()
    {
        $address = new Address();
        $address->postalCode = '750241234';
        $address->streetAddress1 = '6860';

        $card = TestCards::masterCardManual(false, true);

        // 036a authorize
        $response = $card->authorize(17.09)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // 036b capture
        $captureResponse = $response->capture()->execute();
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->responseCode);
    }

    public function testRetail038aAuthorizeDiscoverManual()
    {
        $address = new Address();
        $address->postalCode = '750241234';

        $card = TestCards::discoverManual(false, true);

        $response = $card->authorize(17.10)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // PARTIALLY APPROVED SALE (Required)

    public function testRetail039ChargeDiscoverSwipePartialApproval()
    {
        $card = TestCards::discoverSwipe();

        $response = $card->charge(40.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(40.00, $response->authorizedAmount);
    }

    public function testRetail040ChargeVisaSwipePartialApproval()
    {
        $card = TestCards::visaSwipe();
        $response = $card->charge(130.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(110.00, $response->authorizedAmount);
    }

    public function testRetail041ChargeDiscoverManualPartialApproval()
    {
        $address = new Address();
        $address->postalCode = '75024';
        $card = TestCards::discoverManual(true, true);

        $response = $card->charge(145.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(65.00, $response->authorizedAmount);
    }

    public function testRetail042ChargeMasterCardSwipePartialApproval()
    {
        $card = TestCards::masterCardSwipe();
        $response = $card->charge(155.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(100.00, $response->authorizedAmount);

        // test case 62
        $reversal = $response->reverse(100.00)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reversal);
        $this->assertEquals('00', $reversal->responseCode);
    }

    /*
        SALE WITH GRATUITY
        Tip Edit (Tip at Settlement)
      */

    public function testRetail043ChargeVisaSwipeEditGratuity()
    {
        $card = TestCards::visaSwipe();
        $response = $card->charge(15.12)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $editResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withAmount(18.12)
            ->withGratuity(3.00)
            ->execute();
        $this->assertNotNull($editResponse);
        $this->assertEquals('00', $editResponse->responseCode);
    }

    public function testRetail044ChargeMasterCardManualEditGratuity()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual(true, true);
        $response = $card->charge(15.13)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $editResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withAmount(18.13)
            ->withGratuity(3.00)
            ->execute();
        $this->assertNotNull($editResponse);
        $this->assertEquals('00', $editResponse->responseCode);
    }

    // Tip on Purchase

    public function testRetail045ChargeVisaManualGratuity()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::visaManual(true, true);

        $response = $card->charge(18.61)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withGratuity(3.50)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail046ChargeMasterCardSwipeGratuity()
    {
        $card = TestCards::masterCardSwipe();

        $response = $card->charge(18.62)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withGratuity(3.50)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $editResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withAmount(18.12)
            ->withGratuity(3.00)
            ->execute();
        $this->assertNotNull($editResponse);
        $this->assertEquals('00', $editResponse->responseCode);
    }

    // LEVEL II CORPORATE PURCHASE CARD

    public function testRetail047LevelIIVisaSwipeResponseB()
    {
        $card = TestCards::visaSwipe();

        $response = $card->charge(112.34)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('B', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail047aLevelIIVisaSwipeResponseB()
    {
        $card = TestCards::visaSwipe();

        $response = $card->charge(112.34)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('B', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withTaxType(TaxType::NOT_USED)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail048LevelIIVisaSwipeResponseR()
    {
        $card = TestCards::visaSwipe();

        $response = $card->charge(123.45)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('R', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail049LevelIIVisaManualResponseS()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::visaManual(true, true);

        $response = $card->charge(134.56)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail050LevelIIMasterCardSwipeResponseS()
    {
        $card = TestCards::masterCardSwipe();

        $response = $card->charge(111.06)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail051LevelIIMasterCardManualResponseS()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual(true, true);

        $response = $card->charge(111.07)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail051aLevelIIMasterCardManualResponseS()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual(true, true);
        $response = $card->charge(111.08)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail052LevelIIMasterCardManualResponseS()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::masterCardManual(true, true);
        $response = $card->charge(111.09)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('S', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail053LevelIIAmexSwipeNoResponse()
    {
        $card = TestCards::amexSwipe();
        $response = $card->charge(111.10)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withTaxType(TaxType::SALES_TAX)
            ->withTaxAmount(1)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail054LevelIIAmexManualNoResponse()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::amexManual(true, true);

        $response = $card->charge(111.11)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail055LevelIIAmexManualNoResponse()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::amexManual(true, true);
        $response = $card->charge(111.12)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::NOT_USED)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    public function testRetail055aLevelIIAmexManualNoResponse()
    {
        $address = new Address();
        $address->postalCode = '75024';

        $card = TestCards::amexManual(true, true);
        $response = $card->charge(111.13)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withCommercialRequest(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('0', $response->commercialIndicator);

        $cpcResponse = $response->edit()
            ->withAllowDuplicates(true)
            ->withPoNumber('9876543210')
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();
        $this->assertNotNull($cpcResponse);
        $this->assertEquals('00', $cpcResponse->responseCode);
    }

    // OFFLINE SALE / AUTHORIZATION

    public function testRetail056OfflineChargeVisaManual()
    {
        $card = TestCards::visaManual(false, true);

        $response = $card->charge(15.12)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withOfflineAuthCode('654321')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail056OfflineAuthVisaManual()
    {
        $card = TestCards::visaManual(false, true);

        $response = $card->authorize(15.11)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withOfflineAuthCode('654321')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // RETURN

    public function testRetail057ReturnMasterCard()
    {
        $card = TestCards::masterCardManual(false, true);

        $response = $card->refund(15.11)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail057aReturnMasterCardSwipe()
    {
        $card = TestCards::masterCardSwipe();
        $response = $card->refund(15.15)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail058ReturnJcbTransactionId()
    {
        // See test 14
    }

    // ONLINE VOID / REVERSAL (Required)

    public function testRetail059ReversalVisa()
    {
        // see test 10
    }

    public function testRetail060ReversalMasterCard()
    {
        // see test case 17
    }

    public function testRetail061ReversalMasterCard()
    {
        // see test case 23
    }

    public function testRetail062ReversalMasterCard()
    {
        // see test case 42
    }

    public function testRetail063ReversalVisaPartial()
    {
        // see test case 15
    }

    public function testRetail064ReversalDiscoverPartial()
    {
        // see test 21
    }

    // PIN DEBIT CARD FUNCTIONS

    public function testRetail065DebitSaleVisaSwipe()
    {
        $card = TestCards::asDebit(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(14.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail066DebitSaleMasterCardSwipe()
    {
        $card = TestCards::asDebit(TestCards::masterCardSwipe(), 'F505AD81659AA42A3D123412324000AB');

        $response = $card->charge(14.02)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 71
        $reversal = $response->reverse(14.02)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reversal);
        $this->assertEquals('00', $reversal->responseCode);
    }

    public function testRetail067DebitSaleVisaSwipeCashBack()
    {
        $card = TestCards::asDebit(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(14.03)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(5.00)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail067aDebitSaleMasterCard()
    {
        $card = TestCards::asDebit(TestCards::masterCardSwipe(), 'F505AD81659AA42A3D123412324000AB');

        $response = $card->charge(14.04)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // PARTIALLY APPROVED PURCHASE

    public function testRetail068DebitSaleMasterCardPartialApproval()
    {
        $card = TestCards::asDebit(TestCards::masterCardSwipe(), 'F505AD81659AA42A3D123412324000AB');

        $response = $card->charge(33.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('10', $response->responseCode);
        $this->assertEquals(22.00, $response->authorizedAmount);
    }

    public function testRetail069DebitSaleVisaPartialApproval()
    {
        $this->markTestSkipped();
        
        $card = TestCards::asDebit(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(44.00)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withAllowPartialAuth(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(33.00, $response->authorizedAmount);

        // test case 72
        $reversal = $response->reverse(33.00)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reversal);
        $this->assertEquals('00', $reversal->responseCode);
    }

    // RETURN

    public function testRetail070DebitReturnVisaSwipe()
    {
        $card = TestCards::asDebit(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->refund(14.07)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail070aDebitReturnVisaSwipe()
    {
        $card = TestCards::asDebit(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->refund(14.08)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        $reversalResponse = $response->reverse(14.08)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($reversalResponse);
        $this->assertEquals('00', $reversalResponse->responseCode);
    }

    // REVERSAL

    public function testRetail071DebitReversalMasterCard()
    {
        // see test case 66
    }

    public function testRetail072DebitReversalVisa()
    {
        // see test case 96
    }

    /*
        EBT FUNCTIONS
        Food Stamp Purchase
      */

    public function testRetail080EbtfsPurchaseVisaSwipe()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipe(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(101.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail081EbtfsPurchaseVisaManual()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(102.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Food Stamp Electronic Voucher (Manual Entry Only)

    public function testRetail082EbtVoucherPurchaseVisa()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');
        $card->SerialNumber = '123456789012345';
        $card->approvalCode = '123456';

        $response = $card->charge(103.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Food Stamp Balance Inquiry

    public function testRetail083EbtfsReturnVisaSwipe()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->refund(104.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail084EbtfsReturnVisaManual()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->refund(105.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Food Stamp Balance Inquiry

    public function testRetail085EbtBalanceInquiryVisaSwipe()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail086EbtBalanceInquiryVisaManual()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(true, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /*
        $this->assertEquals('00', $response->responseCode);
        EBT CASH BENEFITS
        Cash Back Purchase
      */

    public function testRetail087EbtCashBackPurchaseVisaSwipe()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(106.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(5.00)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail088EbtCashBackPurchaseVisaManual()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(107.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(5.00)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // No Cash Back Purchase

    public function testRetail089EbtCashBackPurchaseVisaSwipeNoCashBack()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(108.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail090EbtCashBackPurchaseVisaManualNoCashBack()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(109.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Cash Back Balance Inquiry

    public function testRetail091EbtBalanceInquiryVisaSwipeCash()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->balanceInquiry(InquiryType::CASH)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail092EbtBalanceInquiryVisaManualCash()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(true, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->balanceInquiry(InquiryType::CASH)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    // Cash Benefits Withdrawal

    public function testRetail093EbtBenefitWithDrawalVisaSwipe()
    {
        $card = TestCards::asEBTTrack(TestCards::visaSwipeEncrypted(), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(110.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRetail094EbtBenefitWithDrawalVisaManual()
    {
        $card = TestCards::asEBTManual(TestCards::visaManual(false, true), '32539F50C245A6A93D123412324000AA');

        $response = $card->charge(111.01)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->withCashBack(0)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /*
        HMS GIFT - REWARDS
        GIFT
        ACTIVATE
      */

    public function testRetail095ActivateGift1Swipe()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->activate(6.00)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail096ActivateGift2Manual()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->activate(7.00)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    // ADD VALUE

    public function testRetail097AddValueGift1Swipe()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->addValue(8.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail098AddValueGift2Manual()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->activate(9.00)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    // BALANCE INQUIRY

    public function testRetail099BalanceInquiryGift1Swipe()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals(10.00, $response->balanceAmount);
    }

    public function testRetail100BalanceInquiryGift2Manual()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
        $this->assertEquals(10.00, $response->balanceAmount);
    }

    // REPLACE / TRANSFER

    public function testRetail101ReplaceGift1Swipe()
    {
        $oldCard = TestCards::giftCard1Swipe();
        $newCard = TestCards::giftCard2Manual();

        $response = $oldCard->replaceWith($newCard)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail102ReplaceGift2Manual()
    {
        $newCard = TestCards::giftCard1Swipe();
        $oldCard = TestCards::giftCard2Manual();

        $response = $oldCard->replaceWith($newCard)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    // SALE / REDEEM

    public function testRetail103SaleGift1Swipe()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->charge(1.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail104SaleGift2Manual()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->charge(2.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail105SaleGift1VoidSwipe()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->charge(3.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);

        // test case 107
        $voidResponse = $response->void()->execute();
        $this->assertNotNull($voidResponse);
        $this->assertEquals('0', $voidResponse->responseCode);
    }

    public function testRetail106SaleGift2ReversalManual()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->charge(4.00)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);

        //test case 108
        $voidResponse = $response->reverse(4.00)->execute();
        $this->assertNotNull($voidResponse);
        $this->assertEquals('0', $voidResponse->responseCode);
    }

    // VOID

    public function testRetail107VoidGift()
    {
        // see test case 105
    }

    // REVERSAL

    public function testRetail108ReversalGift()
    {
        // see test case 106
    }

    // DEACTIVATE

    public function testRetail109DeactivateGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->deactivate()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    // RECEIPTS MESSAGING

    public function testRetail110ReceiptsMessaging()
    {
        // PRINT AND SCAN RECEIPT FOR TEST 107
    }

    /*
        REWARDS
        BALANCE INQUIRY
      */

    public function testRetail111BalanceInquiryRewards1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
        $this->assertTrue($response->pointsBalanceAmount > 0);
    }

    public function testRetail112BalanceInquiryRewards2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->balanceInquiry()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
        $this->assertTrue($response->pointsBalanceAmount > 0);
    }

    // ALIAS

    public function testRetail113CreateAliasGift1()
    {
        $card = GiftCard::create('9725550100');
        $this->assertNotNull($card);
    }

    public function testRetail114CreateAliasGift2()
    {
        $card = GiftCard::create('9725550100');
        $this->assertNotNull($card);
    }

    public function testRetail115AddAliasGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->addAlias('2145550199')->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail116AddAliasGift2()
    {
        $card = TestCards::giftCard2Manual();

        $response = $card->addAlias('2145550199')->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail117DeleteAliasGift1()
    {
        $card = TestCards::giftCard1Swipe();

        $response = $card->removeAlias('2145550199')->execute();
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->responseCode);
    }

    public function testRetail999CloseBatch()
    {
        try {
            $response = BatchService::closeBatch();
            $this->assertNotNull($response);
            // error_log(sprintf('Batch ID: %s', $response->Id));
            // error_log(sprintf('Sequence Number: %s', $response->SequenceNumber));
        } catch (Exception $e) {
            if (false === strpos($e->getMessage(), static::BATCH_NOT_OPEN)
                && false === strpos($e->getMessage(), static::NO_TRANS_IN_BATCH)
            ) {
                $this->fail($e->getMessage());
            }
        }
    }
}
