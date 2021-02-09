<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\TransITConnector\Certification;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\CardDataSource;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\OperatingEnvironment;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use PHPUnit\Framework\TestCase;

final class Token_Request_Only extends TestCase {
    public function setup() : void {
        ServicesContainer::configureService($this->getConfig());
    }

    public function getConfig() { 
        $config = new TransitConfig();
        $config->merchantId = '887000003226';
        $config->username = 'TA5622118';
        $config->password = 'f8mapGqWrE^rVaA9';
        $config->deviceId = '88700000322601';
        $config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
        $config->developerId = '003226G001';
        $config->acceptorConfig = new AcceptorConfig();
        $config->acceptorConfig->operatingEnvironment = OperatingEnvironment::ON_MERCHANT_PREMISES_ATTENDED;
        $config->acceptorConfig->cardDataSource = CardDataSource::INTERNET;
        return $config;
    }

    public function getMailConfig() {
        $mailConfig = $this->getConfig();
        $mailConfig->acceptorConfig->cardDataSource = CardDataSource::MAIL;
        $mailConfig->acceptorConfig->operatingEnvironment = OperatingEnvironment::ON_MERCHANT_PREMISES_ATTENDED;
        return $mailConfig;
    }

    public function getPhoneConfig() {
        $phoneConfig = $this->getConfig();
        $phoneConfig->acceptorConfig->cardDataSource = CardDataSource::PHONE;
        $phoneConfig->acceptorConfig->operatingEnvironment = OperatingEnvironment::ON_MERCHANT_PREMISES_ATTENDED;
        return $phoneConfig;
    }

    public function test01GenerateVisaMUT() {
        $response = $this->getVisa1()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test02GenerateMasterCardMUT() {
        $response = $this->getMCUnclassifiedTIC()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test03GenerateMasterCardBin2MUT() {
        $response = $this->getMC2BIN()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test04GenerateDiscoverMUT() {
        $response = $this->getDiscover()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test05GenerateAmexMUT() {
        ServicesContainer::configureService($this->getPhoneConfig());

        $response = $this->getAmex()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test06GenerateJCB_MUT() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getJCB()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test07GeneratDiscoverCUP_MUT() {
        ServicesContainer::configureService($this->getMailConfig());

        $response = $this->getDiscoverCUP()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function test08GenerateDinersMUT() {
        ServicesContainer::configureService($this->getPhoneConfig());

        $response = $this->getDiners()->tokenize()->execute();

        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
    }

    public function getVisa1 () {
        $card = new CreditCardData;
        $card->number           = 4012000098765439;
        $card->expYear          = 20; // magic number
        $card->expMonth         = 12;
        $card->cvn              = 999;
        $card->cardType = CardType::VISA;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getVisa2 () {
        $card = new CreditCardData;
        $card->number   = 4012881888818888;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 999;
        $card->cardType = CardType::VISA;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCUnclassifiedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146315000000055;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCSwipeTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312200000035;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMCKeyedTIC () {
        $card = new CreditCardData;
        $card->number   = 5146312620000045;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getMC2BIN () {
        $card = new CreditCardData;
        $card->number   = 2223000048400011;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 998;
        $card->cardType = CardType::MASTERCARD;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getAmex () {
        $card = new CreditCardData;
        $card->number   = 371449635392376;
        $card->expYear  = 25; // magic number
        $card->expMonth = 12;
        $card->cvn      = 9997;
        $card->cardType = CardType::AMEX;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscover () {
        $card = new CreditCardData;
        $card->number   = 6011000993026909;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscoverCUP () {
        $card = new CreditCardData;
        $card->number   = 6282000123842342;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiscoverCUP2 () {
        $card = new CreditCardData;
        $card->number   = 6221261111112650;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DISCOVER;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getDiners () {
        $card = new CreditCardData;
        $card->number   = 3055155515160018;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::DINERS;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getJCB () {
        $card = new CreditCardData;
        $card->number   = 3530142019945859;
        $card->expYear  = 20; // magic number
        $card->expMonth = 12;
        $card->cvn      = 996;
        $card->cardType = CardType::JCB;
        // $card->readerPresent    = true;
        $card->cardPresent      = false;
        return $card;
    }

    public function getAVSData () {
        $address = new Address();
        $address->streetAddress1    = '8320';
        $address->postalCode        = '85284';
        return $address;
    }
}
