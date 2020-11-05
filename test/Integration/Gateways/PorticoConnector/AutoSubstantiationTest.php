<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

final class AutoSubstantiationTest extends TestCase {
    public function setup() : void {
        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';

        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        return $config;
    }

    public function testAutoSub1() {
        $AutoSubAmounts = new AutoSubstantiation();
        $AutoSubAmounts->realTimeSubstantiation = true;
        $AutoSubAmounts->setDentalSubTotal(5.00);
        $AutoSubAmounts->setClinicSubTotal(5);
        $AutoSubAmounts->setVisionSubTotal(5);

        $response = $this->card->charge(15)
            ->withAutoSubstantiation($AutoSubAmounts)
            ->withAllowDuplicates(true)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
}
