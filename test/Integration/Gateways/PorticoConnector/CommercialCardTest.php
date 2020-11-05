<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

final class CommercialCardTest extends TestCase {
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
        $config = new PorticoConfig;
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        return $config;
    }

    public function testCommercialData1() { // test sending LVL2 data with the orinal transaction
        $commercialData = new CommercialData(TaxType::SALES_TAX);
        $commercialData->taxAmount = '1.23';
        $commercialData->poNumber = '654564564';

        $response = $this->card->charge(112.34)
            ->withAllowDuplicates(true)
            ->withCommercialData($commercialData)
            ->withCurrency('USD')
            ->execute();

        $this->assertEquals('B', $response->commercialIndicator);
    }
}

