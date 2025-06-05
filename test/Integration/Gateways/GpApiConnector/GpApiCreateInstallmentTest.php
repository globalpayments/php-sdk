<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\PaymentMethods\{
    Installment,
    CreditCardData
};
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

use PHPUnit\Framework\TestCase;

class GpApiCreateInstallmentTest extends TestCase
{
    public Installment $installment;
    public CreditCardData $visaCard;
    public CreditCardData $masterCard;

    protected function config()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'MX';
        $config->appId = 'bcTDtE6wV2iCfWPqXv0FMpU86YDqvTnc';
        $config->appKey = 'jdf2vlLCA13A3Fsz';
        $config->serviceUrl = 'https://apis-sit.globalpay.com/ucp';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'IPP_Processing';
        $config->accessTokenInfo = $accessTokenInfo;

        return $config;
    }

    public function setup(): void
    {
        $config = $this->config();
        ServicesContainer::configureService($config);

        $this->installment = new Installment();
        $this->installment->channel = 'CNP';
        $this->installment->amount = "11";
        $this->installment->currency = "MXN";
        $this->installment->country = "MX";
        $this->installment->program = "SIP";
        $this->installment->accountName = 'IPP_Processing';
        $this->installment->reference = "TRANS-2019121320901";

        $this->installment->entryMode = "ECOM";

        $this->visaCard = new CreditCardData();
        $this->visaCard->number = "4213168058314147";
        $this->visaCard->expMonth = date('m');
        $this->visaCard->expYear = date('Y', strtotime('+1 year'));
        $this->visaCard->cvn = "123";
        $this->visaCard->cardPresent = false;
        $this->visaCard->readerPresent = false;

        $this->masterCard = new CreditCardData();
        $this->masterCard->number = "5488091111111117";
        $this->masterCard->expMonth = date('m');
        $this->masterCard->expYear = date('Y', strtotime('+1 year'));
        $this->masterCard->cvn = "123";
        $this->masterCard->cardPresent = false;
        $this->masterCard->readerPresent = false;
    }

    public function testCreateInstallment_WithMasterCardAndValidProgramSIP()
    {
        $this->installment->cardDetails = $this->masterCard;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals($this->installment->program, $response->program);
        $this->assertEquals("APPROVAL", $response->message);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("00", $response->result);
        $this->assertEquals("SUCCESS", $response->action->resultCode);
    }

    public function testCreateInstallment_WithMasterCardAndValidProgramMIPP()
    {
        $this->installment->program = 'mIPP';
        $this->installment->cardDetails = $this->masterCard;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals($this->installment->program, $response->program);
        $this->assertEquals("APPROVAL", $response->message);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("00", $response->result);
        $this->assertEquals("SUCCESS", $response->action->resultCode);
    }

    public function testCreateInstallment_WithVisaAndValidProgramSIP()
    {
        $this->installment->cardDetails = $this->visaCard;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals($this->installment->program, $response->program);
        $this->assertEquals("APPROVAL", $response->message);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("00", $response->result);
        $this->assertEquals("SUCCESS", $response->action->resultCode);
    }

    public function testCreateInstallment_WithVisaAndValidProgramMIPP()
    {
        $this->installment->program = 'mIPP';
        $this->installment->cardDetails = $this->visaCard;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals($this->installment->program, $response->program);
        $this->assertEquals("APPROVAL", $response->message);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("00", $response->result);
        $this->assertEquals("SUCCESS", $response->action->resultCode);
    }

    public function testCreateInstallment_WithExpiredVisaCardAndValidProgramMIPP()
    {
        $this->installment->program = 'mIPP';
        $cardData = new CreditCardData();
            $cardData->number = "4213168058314147";
            $cardData->expMonth = date('m');
            $cardData->expYear = 2022;
            $cardData->cvn = "123";
            $cardData->cardPresent = false;
            $cardData->readerPresent = false;
            $this->installment->cardDetails = $cardData;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals("EXPIRED CARD", $response->message);
        $this->assertEquals("54", $response->result);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("DECLINED", $response->action->resultCode);
    }

    public function testCreateInstallment_WithExpiredMasterCardAndValidProgramMIPP()
    {
        $this->installment->program = 'mIPP';
        $cardData = new CreditCardData();
            $cardData->number = "5546259023665054";
            $cardData->expMonth = date('m');
            $cardData->expYear = 2021;
            $cardData->cvn = "123";
            $cardData->cardPresent = false;
            $cardData->readerPresent = false;
            $this->installment->cardDetails = $cardData;
        $response = $this->installment->Create();

        $this->assertNotNull($response);
        $this->assertEquals("EXPIRED CARD", $response->message);
        $this->assertEquals("54", $response->result);
        $this->assertNotNull($response->card->authCode);
        $this->assertEquals("DECLINED", $response->action->resultCode);
    }

    public function testCreateInstallment_WithVisaAndInvalidProgram()
    {
        $this->installment->cardDetails = $this->visaCard;
        $this->installment->program = "InCorrectProgram";
        try {
            $response = $this->installment->Create();
            $this->assertNotNull($response);
        } catch (GatewayException $ex) {
            $this->assertEquals('40213', $ex->responseCode);
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - program contains unexpected data",  $ex->getMessage());
        }
    }

    public function testCreateInstallment_WithMasterCardAndInvalidProgram()
    {
        $this->installment->cardDetails = $this->masterCard;
        $this->installment->program = "InCorrectProgram";
        try {
            $response = $this->installment->Create();
            $this->assertNotNull($response);
        } catch (GatewayException $ex) {
            $this->assertEquals('40213', $ex->responseCode);
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - program contains unexpected data",  $ex->getMessage());
        }
    }
}