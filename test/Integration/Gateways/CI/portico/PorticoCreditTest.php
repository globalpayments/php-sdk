<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\CI\portico;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Utils\CiTestingHarness;
use PHPUnit\Framework\TestCase;

final class PorticoCreditTest extends TestCase
{
    private static CiTestingHarness $harness;
    private CreditCardData $card;

    public static function setUpBeforeClass(): void
    {
        self::$harness = new CiTestingHarness(
            'https://cert.api2.heartlandportico.com',
            CiTestingHarness::LOCKED,
            'PorticoCreditTest'
        );
    }

    public function setUp(): void
    {
        $now = self::$harness->getCurrentTime();
        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = (int) $now->format('n');
        $this->card->expYear = (int) $now->format('Y') + 1;
        $this->card->cvn = '123';
    }

    private function configurePorticoService(?string $uniqueDeviceId = null): void
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTeSAQAfG1UA9qQDrzl-kz4toXvARyieptFwSKP24w';
        $config->developerId = '002914';
        $config->versionNumber = '3026';
        $config->enableLogging = true;
        if ($uniqueDeviceId !== null) {
            // Correction F: no withUniqueDeviceId builder in the PHP SDK; set on config.
            $config->uniqueDeviceId = $uniqueDeviceId;
        }
        $config->serviceUrl = self::$harness->getTestingUrl();
        ServicesContainer::configureService($config);
        self::$harness->reset();
    }

    public function testCreditSale(): void
    {
        self::$harness->setFunction('Portico|Credit Transactions|CreditSale');
        $this->configurePorticoService('5678');
        $clientTxnId = self::$harness->generateRandomId('creditSale');

        $response = $this->card->charge(15.5)
            ->withCurrency('USD')
            ->withClientTransactionId($clientTxnId)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        // Correction G: PHP's Portico connector does not map ClientTxnId onto the
        // transaction response (Java does at PorticoConnector:973; PHP maps it only for
        // reporting). The request still sends $clientTxnId; the echo assert is dropped.
    }

    public function testCreditTxnEdit(): void
    {
        self::$harness->setFunction('Portico|Credit Transactions|CreditTxnEdit - aka Gratuity');
        $this->configurePorticoService();
        $clientTxnId = self::$harness->generateRandomId('creditTxnEdit_charge');

        $charge = $this->card->charge(15)
            ->withCurrency('USD')
            ->withClientTransactionId($clientTxnId)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($charge);
        $this->assertEquals('00', $charge->responseCode);

        // Corrections A + D: edit() is param-less and the ManagementBuilder has
        // no withClientTransactionId in the PHP SDK.
        $edit = $charge->edit()
            ->withAmount(17)
            ->withCurrency('USD')
            ->withGratuity(2)
            ->execute();

        $this->assertNotNull($edit);
        $this->assertEquals('00', $edit->responseCode);
    }

    public function testCreditAdditionalAuth(): void
    {
        self::$harness->setFunction('Portico|Credit Transactions|CreditAdditionalAuth');
        $this->configurePorticoService();
        $clientTxnId = self::$harness->generateRandomId('creditAdditionalAuth_auth');

        $auth = $this->card->authorize(10)
            ->withCurrency('USD')
            ->withClientTransactionId($clientTxnId)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($auth);
        $this->assertEquals('00', $auth->responseCode);
        // Correction G: PHP Portico does not echo ClientTxnId on the transaction response.

        // Correction A: additionalAuth() returns a ManagementBuilder, which has
        // no withClientTransactionId in the PHP SDK.
        $additional = Transaction::fromId($auth->transactionId)
            ->additionalAuth(10)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($additional);
        $this->assertEquals('00', $additional->responseCode);
    }
}
