<?php

namespace GlobalPayments\Api\Tests\Unit\Gateways\RealexConnector;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;

class CreditTest extends TestCase
{
    protected $card;

    public function setup()
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '123';
        $card->cardHolderName = 'Joe Smith';
        $this->card = $card;

        ServicesContainer::configureService($this->getConfig());
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException
     * @expectedExceptionMessage selected gateway does not support this transaction type
     */
    public function testCreditReverse()
    {
        $this->card->reverse(15)
            ->withAllowDuplicates(true)
            ->execute();
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'realexsandbox';
        $config->accountId = 'internet';
        $config->sharedSecret = 'Po8lRRT67a';
        $config->serviceUrl = 'https://test.realexpayments.com/epage-remote.cgi';
        return $config;
    }
}
