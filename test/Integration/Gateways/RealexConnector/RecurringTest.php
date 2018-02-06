<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

// using System;
// using GlobalPayments.Api.Entities;
// using GlobalPayments.Api.PaymentMethods;
// using Microsoft.VisualStudio.TestTools.UnitTesting;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use PHPUnit\Framework\TestCase;

class RecurringTest extends TestCase
{
    /** @var $newCustomer */
    public $newCustomer;

    public function getCustomerId()
    {
        return sprintf("%s-Realex", (new \DateTime())->format("Ymd"));
    }

    public function getPaymentId($type)
    {
        return sprintf("%s-Realex-%s", (new \DateTime())->format("Ymd"), $type);
    }

    protected function config()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "api";
        $config->refundPassword = "refund";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        return $config;
    }

    public function setup()
    {
        ServicesContainer::configure($this->config());

        $this->newCustomer = new Customer();
        $this->newCustomer->key = $this->getCustomerId();
        $this->newCustomer->title = "Mr.";
        $this->newCustomer->firstName = "James";
        $this->newCustomer->lastName = "Mason";
        $this->newCustomer->company = "Realex Payments";
        $this->newCustomer->address = new Address();
        $this->newCustomer->address->streetAddress1 = "Flat 123";
        $this->newCustomer->address->streetAddress2 = "House 456";
        $this->newCustomer->address->streetAddress3 = "The Cul-De-Sac";
        $this->newCustomer->address->city = "Halifax";
        $this->newCustomer->address->province = "West Yorkshire";
        $this->newCustomer->address->pstalCode = "W6 9HR";
        $this->newCustomer->address->country = "United Kingdom";
        $this->newCustomer->homePhone = "+35312345678";
        $this->newCustomer->workPhone = "+3531987654321";
        $this->newCustomer->fax = "+124546871258";
        $this->newCustomer->mobilePhone = "+25544778544";
        $this->newCustomer->email = "text@example.com";
        $this->newCustomer->comments = "Campaign Ref E7373G";
    }

    public function test001aCreateCustomer()
    {
        try {
            $customer = $this->newCustomer.Create();
            $this->assertNotNull($customer);
        } catch (GatewayException $exc) {
            // check for already created
            if ($exc->responseCode != "501") {
                throw $exc;
            }
        }
    }

    public function test001bCreatePaymentMethod()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = 5;
        $card->expYear = 2019;
        $card->cardHolderName = "James Mason";

        try {
            $paymentMethod = $this->newCustomer
                ->addPaymentMethod($this->getPaymentId("Credit"), $card)
                ->create();
            $this->assertNotNull($paymentMethod);
        } catch (GatewayException $exc) {
            // check for already created
            if ($exc->responseCode != "520") {
                throw $exc;
            }
        }
    }

    public function test002aEditCustomer()
    {
        $customer = new Customer();
        $customer->key = $this->getCustomerId();
        $customer->firstName = "Perry";
        $customer->saveChanges();
    }

    public function test002bEditPaymentMethod()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $paymentMethod->paymentMethod = new CreditCardData();
        $paymentMethod->paymentMethod->number = "5425230000004415";
        $paymentMethod->paymentMethod->expMonth = 10;
        $paymentMethod->paymentMethod->expYear = 2020;
        $paymentMethod->paymentMethod->cardHolderName = "Philip Marlowe";
        $paymentMethod->SaveChanges();
    }

    /**
     * @expectedException UnsupportedTransactionException
     */
    public function test003FindOnRealex()
    {
        Customer::Find($this->getCustomerId());
    }

    public function test004aChargeStoredCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $response = $paymentMethod->charge(10)
            ->withCurrency("USD")
            ->withCvn("123")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function test004bVerifyStoredCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $response = $paymentMethod->verify()
            ->withCvn("123")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function test004cRefundStoredCard()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $response = $paymentMethod->refund(10.01)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }

    public function test005DeletePaymentMethod()
    {
        $this->markTestSkipped();
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $paymentMethod->Delete();
    }

    public function test006RecurringPayment()
    {
        $paymentMethod = new RecurringPaymentMethod($this->getCustomerId(), $this->getPaymentId("Credit"));
        $response = $paymentMethod->charge(12)
            ->withRecurringInfo(RecurringType::FIXED, RecurringSequence::FIRST)
            ->withCurrency("USD")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);
    }
}
