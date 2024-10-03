<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\{ThreeDSecure, Transaction};
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\{SampleRequestLogger, Logger};
use PHPUnit\Framework\TestCase;

final class PorticoEcommerceTests extends TestCase
{
    private CreditCardData $card;

    public function setup(): void
    {
        ServicesContainer::configureService($this->getConfig());

        $this->card = new CreditCardData();
        $this->card->number = '4012002000060016';
        $this->card->expMonth = 12;
        $this->card->expYear = 2025;
        $this->card->cvn = '811';
        $this->card->cardPresent = false;
        $this->card->readerPresent = false;
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTnHBQBkVnIApt5_DIG_OTix0zXDR-7UQMAx6focuA';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        return $config;
    }

    public function testEcomWithSecure3D_03DefaultVersionOne()
    {
        $ecom = new ThreeDSecure();
        $ecom->cavv = 'XXXXf98AAajXbDRg3HSUMAACAAA=';
        $ecom->xid = '0l35fwh1sys3ojzyxelu4ddhmnu5zfke5vst';
        $ecom->eci = 5;
        $ecom->setVersion(Secure3dVersion::ONE);
        $this->card->threeDSecure = $ecom; 

        /** @var Transaction */
        $response = $this->card->charge('10')
            ->withCurrency('USD')
            ->withInvoiceNumber('1234567890')
            ->withAllowDuplicates(true)
            ->execute();

        /** @var TransactionSummary */
        $txnDetails = ReportingService::transactionDetail($response->transactionId)->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($txnDetails);
        
        $this->assertEquals("00", $response->responseCode);

        $this->assertNotNull($txnDetails->threeDSecure->authenticationValue);
        $this->assertNotNull($txnDetails->threeDSecure->directoryServerTransactionId);
        $this->assertNotNull($txnDetails->threeDSecure->eci);
        $this->assertNotNull($txnDetails->threeDSecure->getVersion());
    }

    public function testEcomWithSecure3D_03Version2()
    {
        $ecom = new ThreeDSecure();
        $ecom->cavv = 'XXXXf98AAajXbDRg3HSUMAACAAA=';
        $ecom->xid = '0l35fwh1sys3ojzyxelu4ddhmnu5zfke5vst';
        $ecom->eci = 5;
        $ecom->setVersion(Secure3dVersion::TWO);
        $this->card->threeDSecure = $ecom;

        /** @var Transaction */
        $response = $this->card->charge('10')
            ->withCurrency('USD')
            ->withInvoiceNumber('1234567890')
            ->withAllowDuplicates(true)
            ->execute();

        /** @var TransactionSummary */
        $txnDetails = ReportingService::transactionDetail($response->transactionId)->execute();

        $this->assertNotNull($response);
        $this->assertNotNull($txnDetails);
        
        $this->assertEquals("00", $response->responseCode);

        $this->assertNotNull($txnDetails->threeDSecure->authenticationValue);
        $this->assertNotNull($txnDetails->threeDSecure->directoryServerTransactionId);
        $this->assertNotNull($txnDetails->threeDSecure->eci);
        $this->assertNotNull($txnDetails->threeDSecure->getVersion());
    }
}