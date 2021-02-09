<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use PHPUnit\Framework\TestCase;

class TransITDebitTest extends TestCase
{
    protected $track;

    public function setup()
    {
        $this->track = new DebitTrackData();
        
        $encryptionData = new EncryptionData();
        $encryptionData->version = '01';
        $encryptionData->ksn = '000000000000000';
        
        $this->track->encryptionData = $encryptionData;
        $this->track->pinBlock = '0000';
        $this->track->setValue('<E1050711%4012002000060016^VI TEST CREDIT^25121011803939600000?|LO04K0WFOmdkDz0um+GwUkILL8ZZOP6Zc4rCpZ9+kg2T3JBT4AEOilWTI|+++++++Dbbn04ekG|11;4012002000060016=25121011803939600000?|1u2F/aEhbdoPixyAPGyIDv3gBfF|+++++++Dbbn04ekG|00|||/wECAQECAoFGAgEH2wYcShV78RZwb3NAc2VjdXJlZXhjaGFuZ2UubmV0PX50qfj4dt0lu9oFBESQQNkpoxEVpCW3ZKmoIV3T93zphPS3XKP4+DiVlM8VIOOmAuRrpzxNi0TN/DWXWSjUC8m/PI2dACGdl/hVJ/imfqIs68wYDnp8j0ZfgvM26MlnDbTVRrSx68Nzj2QAgpBCHcaBb/FZm9T7pfMr2Mlh2YcAt6gGG1i2bJgiEJn8IiSDX5M2ybzqRT86PCbKle/XCTwFFe1X|>;');

        ServicesContainer::configureService($this->getConfig());
    }

    protected function getConfig()
    {
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322602';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
        $config->developerId = '003226G001';
        $config->acceptorConfig = new AcceptorConfig();
        return $config;
    }
    
    public function testDebitSale()
    {
        $response = $this->track->charge(10)
            ->withCurrency('USD')
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
}
