<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\{Channel, TransactionStatus};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};
use PHPUnit\Framework\TestCase;

class GpApiVisaAftTest extends TestCase
{
    private CreditCardData $card;
    private string $currency = 'GBP';

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testVisaAftSale()
    {
        $transaction = $this->card->charge(42)
            ->withCurrency($this->currency)
            ->withSupplementaryData('VISA_DIRECT_AFT', [
                'John Smith',
                '10 High Street',
                'Nottingham',
                'GBR',
                '02',
                '123456789'
            ])
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
    }

    public function testVisaAftInvalidType()
    {
        $transaction = $this->card->charge(42)
            ->withCurrency($this->currency)
            ->withSupplementaryData('INVALID_TYPE', [
                'John Smith',
                '10 High Street',
                'Nottingham',
                'GBR',
                '02',
                '123456789'
            ])
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
    }

    public function testVisaAftEmptyData()
    {
        $transaction = $this->card->charge(42)
            ->withCurrency($this->currency)
            ->withSupplementaryData('VISA_DIRECT_AFT', [])
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
    }

    public function testVisaAftNullSupplementaryData()
    {
        $transaction = $this->card->charge(42)
            ->withCurrency($this->currency)
            ->withSupplementaryData('VISA_DIRECT_AFT', null)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
    }

    public function testVisaAftMissingCurrency()
    {
        $exceptionCaught = false;
        try {
            $this->card->charge(42)
                ->withSupplementaryData('VISA_DIRECT_AFT', [
                    'John Smith',
                    '10 High Street',
                    'Nottingham',
                    'GBR',
                    '02',
                    '123456789'
                ])
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString('MANDATORY_DATA_MISSING', $e->getMessage());
            $this->assertStringContainsString('currency', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testVisaAftInvalidCurrency()
    {
        $exceptionCaught = false;
        try {
            $this->card->charge(42)
                ->withCurrency('XYZ')
                ->withSupplementaryData('VISA_DIRECT_AFT', [
                    'John Smith',
                    '10 High Street',
                    'Nottingham',
                    'GBR',
                    '02',
                    '123456789'
                ])
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString('SYSTEM_ERROR', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testVisaAftZeroAmount()
    {
        $exceptionCaught = false;
        try {
            $this->card->charge(0)
                ->withCurrency($this->currency)
                ->withSupplementaryData('VISA_DIRECT_AFT', [
                    'John Smith',
                    '10 High Street',
                    'Nottingham',
                    'GBR',
                    '02',
                    '123456789'
                ])
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString('MANDATORY_DATA_MISSING', $e->getMessage());
            $this->assertStringContainsString('amount', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function setUpConfig(): GpApiConfig
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'GB';
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }
}
