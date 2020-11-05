<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class AchTest extends TestCase
{
    protected $eCheck;
    protected $SUPTeCheck;
    protected $address;
    private $enableCryptoUrl = true;

    public function setup()
    {
        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '1357902468';
        $this->eCheck->routingNumber = '122000030';
        $this->eCheck->checkType = CheckType::PERSONAL;
        $this->eCheck->secCode = SecCode::PPD;
        $this->eCheck->accountType = AccountType::CHECKING;
        $this->eCheck->entryMode = EntryMethod::MANUAL;
        $this->eCheck->checkHolderName = 'John Doe';
        $this->eCheck->driversLicenseNumber = '09876543210';
        $this->eCheck->driversLicenseState = 'TX';
        $this->eCheck->phoneNumber = '8003214567';
        $this->eCheck->birthYear = '1997';
        $this->eCheck->ssnLast4 = '4321';

        $this->SUPTeCheck = new ECheck();
        $this->SUPTeCheck->token = $this->getACHToken();
        $this->SUPTeCheck->checkType = CheckType::PERSONAL;
        $this->SUPTeCheck->secCode = SecCode::PPD;
        $this->SUPTeCheck->accountType = AccountType::CHECKING;
        $this->SUPTeCheck->checkHolderName = 'John Doe';

        $this->address = new Address();
        $this->address->streetAddress1 = '123 Main St.';
        $this->address->city = 'Downtown';
        $this->address->state = 'NJ';
        $this->address->postalCode = '12345';

        ServicesContainer::configureService($this->getConfig());
    }

    public function testCheckSale()
    {
        $response = $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testSUPTCheckSale()
    {
        $response = $this->SUPTeCheck->charge('11.01')
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    protected function getConfig()
    {
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }

    protected function getACHToken()
    {
        $payload = array(
            'object' => 'token',
            'token_type' => 'supt',
            'ach' => array(
                'account_number'    => '1357902468',
                'routing_number'    => '122000030',
            ),
        );
        $url = 'https://cert.api2-c.heartlandportico.com/Hps.Exchange.PosGateway.Hpf.v1/api/token?api_key=pkapi_cert_jKc1FtuyAydZhZfbB3';
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($payload),
            ),
        );
        $context = stream_context_create($options);
        $response = json_decode(file_get_contents($url, false, $context));
        if (!$response || isset($response->error)) {
            $this->fail('no single-use token obtained');
        }
        return $response->token_value;
    }
}
