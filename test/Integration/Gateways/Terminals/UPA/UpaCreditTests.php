<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\{
    LodgingData,
    AutoSubstantiation
};
use GlobalPayments\Api\Terminals\Enums\{
    DeviceType,
    ConnectionModes
};
use GlobalPayments\Api\Terminals\{
    ConnectionConfig,
    TerminalResponse
};
use GlobalPayments\Api\Entities\Enums\{
    TaxType,
    ExtraChargeType,
    ManualEntryMethod,
    StoredCredentialInitiator
};
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;

use DateTime;
use PHPUnit\Framework\TestCase;

class UpaCreditTests extends TestCase
{
    private IDeviceInterface $device;
    private CreditCardData $card;

    /**
     * @throws ApiException
     */
    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());

        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'Joe Smith';
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig(): ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.71.118';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testCreditSaleSwipe()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    /*
     * Note: EMV cards needs to be used for this test case
     */
    public function testCreditSaleEMV()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        //EMV
        $this->assertNotNull($response->applicationPreferredName);
        $this->assertNotNull($response->applicationLabel);
        $this->assertNotNull($response->applicationId);
        $this->assertNotNull($response->applicationCryptogramType);
        $this->assertNotNull($response->applicationCryptogram);
        $this->assertNotNull($response->cardHolderVerificationMethod);
        $this->assertNotNull($response->terminalVerificationResults);
    }

    public function testCreditVoid()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);

        sleep(15);

        $voidResponse = $this->device->void()
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->deviceResponseCode);
    }

    public function testSaleRefund()
    {
        $refundResponse = $this->device->refund(10)
            ->execute();

        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }

    public function testCreditRefund_ByTransactionId()
    {
        $saleResponse = $this->device->sale(1)
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
        $this->assertNotNull($saleResponse->transactionId);

        $refundResponse = $this->device->refund(1)
            ->withTransactionId($saleResponse->transactionId)
            ->execute();
      
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }

    public function testTipAdjust_withTerminalRefNumber()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $adjust = $this->device->tipAdjust(1.05)
            ->withTerminalRefNumber($response->terminalRefNumber)
            ->execute();

        $this->assertNotNull($adjust);
        $this->assertEquals('00', $adjust->deviceResponseCode);
    }

    public function testTipAdjust_withReferenceNo()
    {
        $response = $this->device->sale(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $adjust = $this->device->tipAdjust(1.05)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($adjust);
        $this->assertEquals('00', $adjust->deviceResponseCode);
    }

    public function testCardVerify()
    {
        $response = $this->device->verify()
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testSaleReversal()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);

        sleep(15);

        $reverseResponse = $this->device->reverse()
            ->withTerminalRefNumber($response->terminalRefNumber)
            ->withAmount('10.00')
            ->withEcrId('1')
            ->execute();

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('00', $reverseResponse->deviceResponseCode);
    }

    public function testVerifyWithTokenRequest()
    {
        $response = $this->device->verify()
            ->withRequestMultiUseToken(1)
            ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
    }

    public function testPreAuth()
    {
        $response = $this->device->authorize(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $this->assertNotNull($response->transactionId);

        $captureResponse = $this->device->capture(10)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
    }

    public function testAuthCompletion()
    {
        $response = $this->device->capture(10)
            ->withTransactionId("0157")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testHealthCareCardSale()
    {
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->realTimeSubstantiation = true;
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);

        $response = $this->device->sale(10)
            ->withAutoSubstantiation($autoSubAmounts)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testCreditTokenRequest()
    {
        $response = $this->device->tokenize()
            ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);

        $saleResponse = $this->device->sale(10)
            ->withToken($response->token)
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
    }

    public function testCardVerifyTokenSale()
    {
        $response = $this->device->verify()
            ->withRequestMultiUseToken(1)
            ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
        $this->assertNotNull($response->cardBrandTransId);

        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->realTimeSubstantiation = true;
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);

        $saleResponse = $this->device->sale(100)
            ->withToken($response->token)
            ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
            ->withAutoSubstantiation($autoSubAmounts)
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
    }

    public function testDeletePreAuth()
    {
        $response = $this->device->deletePreAuth()
            ->withEcrId(13)
            ->withTransactionId('200015214831')
            ->withAmount(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testMailOrder()
    {
        $trnAmount = 10;
        $this->card->entryMethod = ManualEntryMethod::MAIL;
        $response = $this->device->sale($trnAmount)
            ->withEcrId(12)
            ->withTaxAmount(2.18)
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->withProcessCPC(true)
            ->withRequestMultiUseToken(1)
            ->withInvoiceNumber('123A10')
            ->withPaymentMethod($this->card)
            ->withAllowDuplicates(true)
            ->withCardOnFileIndicator(StoredCredentialInitiator::CARDHOLDER)
            ->withCardBrandTransId("transId")
            ->withShippingDate(new DateTime())
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals($trnAmount, $response->transactionAmount);
    }

    public function testUpdateTaxInfo()
    {
        $response = $this->device->updateTaxInfo(14.56)
            ->withTerminalRefNumber('0149')
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testUpdateLodgingDetails()
    {
        $lodgingData = new LodgingData();
        $lodgingData->folioNumber = '1';
        $lodgingData->extraCharges = [ExtraChargeType::RESTAURANT, ExtraChargeType::OTHER];

        $response = $this->device->updateLodgingDetails(20)
            ->withTransactionId('1676654133')
            ->withLodgingData($lodgingData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testLogon()
    {
        $response = $this->device->logon();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals(UpaMessageId::LOGON, $response->command);
    }

    public function testForceSale()
    {
        $trnAmount = 10;
        $this->card->entryMethod = ManualEntryMethod::PHONE;

        $response = $this->device->sale($trnAmount)
            ->withEcrId(12)
            ->withPaymentMethod($this->card)
            ->withTaxAmount(2.18)
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->withGratuity(12.56)
            ->withInvoiceNumber('123456789012345')
            ->withAllowDuplicates(true)
            ->withConfirmationAmount(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('FORCE SALE', $response->transactionType);
    }
}
