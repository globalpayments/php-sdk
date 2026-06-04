<?php

namespace Gateways\GpApiConnector;

require_once __DIR__ . '/GpApiApacBaseTest.php';

use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

class GpApiCaptureApacTest extends GpApiApacBaseTest
{
    private function configureService(string $country): string
    {
        return parent::configureServiceForCountry('apac-capture-', $country);
    }

    private function assertCapture(
        CreditCardData $card,
        float $authAmount,
        float $captureAmount,
        string $currency,
        string $serviceName
    ): void {
        $auth = $card->authorize($authAmount)
            ->withCurrency($currency)
            ->withAllowDuplicates(true)
            ->execute($serviceName);

        $this->assertEquals('SUCCESS', $auth->responseCode, 'Auth response code must be SUCCESS');
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $auth->responseMessage, 'Auth status must be PREAUTHORIZED');
        $this->assertNotEmpty($auth->transactionId, 'Auth transaction ID must be present');

        $capture = $auth->capture($captureAmount)->execute($serviceName);

        $this->assertNotNull($capture, 'Capture response must not be null');
        $this->assertEquals('SUCCESS', $capture->responseCode, 'Capture response code must be SUCCESS');
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage, 'Capture status must be CAPTURED');
        $this->assertNotEmpty($capture->transactionId, 'Capture transaction ID must be present');
    }

    public function captureScenarioProvider(): array
    {
        return parent::apacScenarioProvider();
    }

    /**
     * @dataProvider captureScenarioProvider
     */
    public function testCaptureAcrossApacRegions(string $country, string $currency, string $brand): void
    {
        $service = $this->configureService($country);
        $card = $this->createCard($brand);

        $this->assertCapture($card, 10.00, 7.00, $currency, $service);
    }
}
