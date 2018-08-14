<?php

namespace GlobalPayments\Api\Test\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

class HppTestCase extends TestCase
{

    /** @var HostedService */
    protected $service;

    /** @var RealexHppClient */
    protected $client;

    protected function config()
    {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";

        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        return $config;
    }

    public function setup()
    {
        $this->service = new HostedService($this->config());
    }

    /* 10. ThreedSecureResponse */

    public function testThreedSecureResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService(
            $config
        );

        //response
        // TODO: grab the response JSON from the client-side for example:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"hpp","ORDER_ID":"OTA4NUEzOEEtMkE3RjU2RQ","TIMESTAMP":"20180724124150","RESULT":"00","PASREF":"15324325098818233","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":"123|56","BILLING_CO":"IRELAND","ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":null,"SHA1HASH":"d1ff806b449b86375dbda74e2611760c348fcdeb","DCC_INFO_REQUST":null,"DCC_INFO_RESPONSE":null,"HPP_FRAUD_FILTER_MODE":null,"TSS_INFO":null}';

        $parsedResponse = $service->parseResponse($responseJson);
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $eci = $responseValues["ECI"]; // 5 - fully authenticated
        $cavv = $responseValues["CAVV"]; // AAACBUGDZYYYIgGFGYNlAAAAAAA=
        $xid = $responseValues["XID"]; // vJ9NXpFueXsAqeb4iAbJJbe+66s=
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 02. ProcessPaymentConsumeHppResponse */

    public function testprocessPaymentConsumeResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService($config);

        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"hpp","ORDER_ID":"NjMwNkMxMTAtMTA5RUNDRQ","TIMESTAMP":"20180720104340","RESULT":"00","PASREF":"15320798200414985","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":"123|56","BILLING_CO":"IRELAND","ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"100","SHA1HASH":"32628cf3f887ab9f4f1c547a10ac365c2168f0e2","DCC_INFO":null,"HPP_FRAUD_FILTER_MODE":null,"TSS_INFO":null}';

        // create the response object from the response JSON
        $parsedResponse = $service->parseResponse($responseJson);

        $orderId = $parsedResponse->orderId; // GTI5Yxb0SumL_TkDMCAxQA
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        //$fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // PASS

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 06. CardStorageCreatePayerStoreCardResponse */

    public function testCardStorageCreatePayerStoreCardResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService(
            $config
        );

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"3dsecure","ORDER_ID":"NTgxMkMzODUtNTEwMkNCMw","TIMESTAMP":"20180723110112","RESULT":"00","PASREF":"15323400720177562","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":null,"BILLING_CO":null,"ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"1500","SHA1HASH":"4c7a635401c57371a0931bb3a21a849181cc963d","DCC_INFO":null,"HPP_FRAUD_FILTER_MODE":null,"TSS_INFO":null}';

        $parsedResponse = $service->parseResponse($responseJson);
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        /*
          // Payer Setup Details
          $payerSetupResult = $responseValues["PAYER_SETUP"]; // 00
          $payerSetupMessage = $responseValues["PAYER_SETUP_MSG"]; // Successful
          $payerReference = $responseValues["SAVED_PAYER_REF"]; // 5e7e9152-2d53-466d-91bc-6d12ebc56b79
          // Card Setup Details
          $cardSetupResult = $responseValues["PMT_SETUP"]; // 00
          $cardSetupMessage = $responseValues["PMT_SETUP_MSG"]; // Successful
          $cardReference = $responseValues["SAVED_PMT_REF"]; // ca68dcac-9af2-4d65-b06c-eb54667dcd4a
          // Card Details Stored
          $cardType = $responseValues["SAVED_PMT_TYPE"]; // MC
          $cardDigits = $responseValues["SAVED_PMT_DIGITS"]; // 542523xxxx4415
          $cardExpiry = $responseValues["SAVED_PMT_EXPDATE"]; // 1025
          $cardName = $responseValues["SAVED_PMT_NAME"]; // James Mason
         */
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 08. CardStorageDisplayStoredCardsResponse */

    public function testCardStorageDisplayStoredCardsResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService(
            $config
        );

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = array("MERCHANT_ID" => "MerchantId", "ACCOUNT" => "internet", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "843680654f377bfa845387fdbace35acc9d95778", "RESULT" => "00", "AUTHCODE" => "12345", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642");

        $parsedResponse = $service->parseResponse(json_encode($responseJson));
        $responseCode = $parsedResponse->responseCode; // 00
        $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
        $responseValues = $parsedResponse->responseValues; // get values accessible by key
        // card used to complete payment, edited or deleted
        $chosenCard = $responseValues["HPP_CHOSEN_PMT_REF"]; // 099efeb4-eda2-4fd7-a04d-29647bb6c51d
        $editedCard = $responseValues["HPP_EDITED_PMT_REF"]; // 037bd26a-c76b-4ee4-8063-376d8858f23d
        $deletedCard = $responseValues["HPP_DELETED_PMT_REF"]; // 3db4c72c-cd95-4743-8070-f17e2b56b642
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 12. FraudManagementResponse */

    public function testFraudManagementResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService(
            $config
        );

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = array("MERCHANT_ID" => "MerchantId", "ACCOUNT" => "internet", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "843680654f377bfa845387fdbace35acc9d95778", "RESULT" => "00", "AUTHCODE" => "12345", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "HOLD", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS");
        ;

        $parsedResponse = $service->parseResponse(json_encode($responseJson));
        $responseCode = $parsedResponse->responseCode; // 00
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // HOLD
        $cardRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe"]; // HOLD
        $ipRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305"]; // PASS
        // TODO: update your application and display transaction outcome to the customer

        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }

    /* 14. DynamicCurrencyConversionResponse */

    public function testDynamicCurrencyConversionResponse()
    {
        $config = new ServicesConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'hpp';
        $config->sharedSecret = 'secret';
        $config->serviceUrl = 'https://pay.sandbox.realexpayments.com/pay';

        $service = new HostedService(
            $config
        );

        // TODO: grab the response JSON from the client-side for example:
        //sample response JSON:
        $responseJson = '{"MERCHANT_ID":"heartlandgpsandbox","ACCOUNT":"apidcc","ORDER_ID":"NTQyQzgxREMtMzVFQzlDNw","TIMESTAMP":"20180724095953","RESULT":"00","PASREF":"15324227932436743","AUTHCODE":"12345","AVSPOSTCODERESULT":"U","CVNRESULT":"U","HPP_LANG":"GB","SHIPPING_CODE":null,"SHIPPING_CO":null,"BILLING_CODE":null,"BILLING_CO":null,"ECI":null,"CAVV":null,"XID":null,"MERCHANT_RESPONSE_URL":"http:\/\/requestb.in\/10q2bjb1","CARD_PAYMENT_BUTTON":null,"MESSAGE":"[ test system ] Authorised","AMOUNT":"100100","SHA1HASH":"320c7ddc49d292f5900c676168d5cc1f2a55306c","DCC_INFO_REQUST":{"CCP":"Fexco","TYPE":1,"RATE":"1.7203","RATE_TYPE":"S","AMOUNT":"172202","CURRENCY":"AUD"},"DCC_INFO_RESPONSE":{"cardHolderCurrency":"AUD","cardHolderAmount":"172202","cardHolderRate":"1.7203","merchantCurrency":"EUR","merchantAmount":"100100","marginRatePercentage":"","exchangeRateSourceName":"","commissionPercentage":"","exchangeRateSourceTimestamp":""},"HPP_FRAUD_FILTER_MODE":null,"TSS_INFO":null}';
        $parsedResponse = $service->parseResponse($responseJson);

        $responseCode = $parsedResponse->responseCode; // 00
        $responseValues = $parsedResponse->responseValues; // get values accessible by key

        $conversionProcessor = $responseValues['DCC_INFO_REQUST']["CCP"]; // fexco
        $conversionRate = $responseValues['DCC_INFO_REQUST']["RATE"]; // 1.7203
        $merchantAmount = $responseValues['DCC_INFO_RESPONSE']["merchantAmount"]; // 1999
        $cardholderAmount = $responseValues['DCC_INFO_RESPONSE']["cardHolderAmount"]; // 3439
        $merchantCurrency = $responseValues['DCC_INFO_RESPONSE']["merchantCurrency"]; // EUR
        $cardholderCurrency = $responseValues['DCC_INFO_RESPONSE']["cardHolderCurrency"]; // AUD
        $marginPercentage = $responseValues['DCC_INFO_RESPONSE']["marginRatePercentage"]; // 3.75
        $exchangeSource = $responseValues['DCC_INFO_RESPONSE']["exchangeRateSourceName"]; // REUTERS WHOLESALE INTERBANK
        $commissionPercentage = $responseValues['DCC_INFO_RESPONSE']["commissionPercentage"]; // 0
        $exchangeTimestamp = $responseValues['DCC_INFO_RESPONSE']["exchangeRateSourceTimestamp"]; // 20170518162700
        // TODO: update your application and display transaction outcome to the customer
        $this->assertNotEquals(null, $parsedResponse);
        $this->assertEquals("00", $responseCode);
    }
}
