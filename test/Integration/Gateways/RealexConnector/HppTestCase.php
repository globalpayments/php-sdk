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

class HppTestCase extends TestCase {
    /* 10. ThreedSecureResponse */

    public function testthreedSecureResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "10968fa6a0c949a932e7022960420463893fd3c2", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "HOLD", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "Successful", "PMT_SETUP_MSG" => "Successful", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS", "PAYER_SETUP" => "00", "SAVED_PAYER_REF" => "5e7e9152-2d53-466d-91bc-6d12ebc56b79", "PMT_SETUP" => "00", "SAVED_PMT_REF" => "ca68dcac-9af2-4d65-b06c-eb54667dcd4a", "SAVED_PMT_TYPE" => "MC", "SAVED_PMT_DIGITS" => "542523xxxx4415", "SAVED_PMT_EXPDATE" => "1025", "SAVED_PMT_NAME" => "James Mason");

        try {
            $parsedResponse = $service->parseResponse($responseJson);
            $responseCode = $parsedResponse["responseCode"]; // 00
            $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
            $responseValues = $parsedResponse->responseValues; // get values accessible by key

            $eci = $responseValues["ECI"]; // 5 - fully authenticated
            $cavv = $responseValues["CAVV"]; // AAACBUGDZYYYIgGFGYNlAAAAAAA=
            $xid = $responseValues["XID"]; // vJ9NXpFueXsAqeb4iAbJJbe+66s=
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $parsedResponse);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // TODO: add your error handling here
        }
    }

    /* 02. ProcessPaymentConsumeHppResponse */

    public function testprocessPaymentConsumeResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "63dcf896a5bb334c6321a8fac3555bd192f897cb95a6ddc848d0cf23e6c0de97bab4c30f84d36e95161ad8df120e6627a8845f61c5988a8746f759c5f839b6ea", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "Successful", "PMT_SETUP_MSG" => "Successful", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS", "PAYER_SETUP" => "00", "SAVED_PAYER_REF" => "5e7e9152-2d53-466d-91bc-6d12ebc56b79", "PMT_SETUP" => "00", "SAVED_PMT_REF" => "ca68dcac-9af2-4d65-b06c-eb54667dcd4a", "SAVED_PMT_TYPE" => "MC", "SAVED_PMT_DIGITS" => "542523xxxx4415", "SAVED_PMT_EXPDATE" => "1025", "SAVED_PMT_NAME" => "James Mason");

        try {
            // create the response object from the response JSON
            $parsedResponse = $service->parseResponse($responseJson);
            $orderId = $parsedResponse->orderId; // GTI5Yxb0SumL_TkDMCAxQA
            $responseCode = $parsedResponse->responseCode; // 00
            $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
            $responseValues = $parsedResponse->responseValues; // get values accessible by key
            $fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // PASS

            /* TODO: simple example to check the request vs. response data before updating the DB
              grab the transaction request details from the DB using, for example, the order id:
              $requestAmountFromDb = get data from DB using orderId;
              $requestTimestampFromDb = get data from DB using orderId;

              only update the DB result as successful if amount and timestamp match the original request
              if ($responseCode == "00" && $requestAmountFromDb == $parsedResponse->authorizedAmount && $requestTimestampFromDb == $responseValues["TIMESTAMP"])
              {
              // update DB with successful result
              }
             */

            $this->assertNotEquals(null, $parsedResponse);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // For example if the SHA1HASH doesn't match what is expected
            // TODO: add your error handling here
        }
    }

    /* 06. CardStorageCreatePayerStoreCardResponse */

    public function testcardStorageCreatePayerStoreCardResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "63dcf896a5bb334c6321a8fac3555bd192f897cb95a6ddc848d0cf23e6c0de97bab4c30f84d36e95161ad8df120e6627a8845f61c5988a8746f759c5f839b6ea", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "Successful", "PMT_SETUP_MSG" => "Successful", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS", "PAYER_SETUP" => "00", "SAVED_PAYER_REF" => "5e7e9152-2d53-466d-91bc-6d12ebc56b79", "PMT_SETUP" => "00", "SAVED_PMT_REF" => "ca68dcac-9af2-4d65-b06c-eb54667dcd4a", "SAVED_PMT_TYPE" => "MC", "SAVED_PMT_DIGITS" => "542523xxxx4415", "SAVED_PMT_EXPDATE" => "1025", "SAVED_PMT_NAME" => "James Mason");

        try {
            $parsedResponse = $service->parseResponse($responseJson);
            $responseCode = $parsedResponse->responseCode; // 00
            $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
            $responseValues = $parsedResponse->responseValues; // get values accessible by key
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
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $parsedResponse);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // For example if the SHA1HASH doesn't match what is expected
            // TODO: add your error handling here
        }
    }

    /* 08. CardStorageDisplayStoredCardsResponse */

    public function testcardStorageDisplayStoredCardsResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "63dcf896a5bb334c6321a8fac3555bd192f897cb95a6ddc848d0cf23e6c0de97bab4c30f84d36e95161ad8df120e6627a8845f61c5988a8746f759c5f839b6ea", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "Successful", "PMT_SETUP_MSG" => "Successful", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS", "PAYER_SETUP" => "00", "SAVED_PAYER_REF" => "5e7e9152-2d53-466d-91bc-6d12ebc56b79", "PMT_SETUP" => "00", "SAVED_PMT_REF" => "ca68dcac-9af2-4d65-b06c-eb54667dcd4a", "SAVED_PMT_TYPE" => "MC", "SAVED_PMT_DIGITS" => "542523xxxx4415", "SAVED_PMT_EXPDATE" => "1025", "SAVED_PMT_NAME" => "James Mason");

        try {
            $parsedResponse = $service->parseResponse($responseJson);
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
        } catch (ApiException $e) {
            // For example if the SHA1HASH doesn't match what is expected
            // TODO: add your error handling here
        }
    }

    /* 12. FraudManagementResponse */

    public function testfraudManagementResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "63dcf896a5bb334c6321a8fac3555bd192f897cb95a6ddc848d0cf23e6c0de97bab4c30f84d36e95161ad8df120e6627a8845f61c5988a8746f759c5f839b6ea", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "HOLD", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "SUCCESS", "PMT_SETUP_MSG" => "SUCCESS", "SAVED_PMT_TYPE" => "VISA", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes", "HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe" => "HOLD", "HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305" => "PASS");

        try {
            $parsedResponse = $service->parseResponse($responseJson);
            $responseCode = $parsedResponse->responseCode; // 00
            $responseValues = $parsedResponse->responseValues; // get values accessible by key

            $fraudFilterResult = $responseValues["HPP_FRAUDFILTER_RESULT"]; // HOLD
            $cardRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_56257838-4590-4227-b946-11e061fb15fe"]; // HOLD
            $ipRuleResult = $responseValues["HPP_FRAUDFILTER_RULE_cf609cf9-9e5a-4700-ac69-8aa09c119305"]; // PASS
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $parsedResponse);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // For example if the SHA1HASH doesn't match what is expected
            // TODO: add your error handling here
        }
    }

    /* 14. DynamicCurrencyConversionResponse */

    public function testdynamicCurrencyConversionResponse() {
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
        $responseJson = array("MERCHANT_ID" => "heartlandgpsandbox", "ACCOUNT" => "hpp", "ORDER_ID" => "GTI5Yxb0SumL_TkDMCAxQA", "AMOUNT" => "1999", "TIMESTAMP" => "20170725154824", "SHA1HASH" => "63dcf896a5bb334c6321a8fac3555bd192f897cb95a6ddc848d0cf23e6c0de97bab4c30f84d36e95161ad8df120e6627a8845f61c5988a8746f759c5f839b6ea", "RESULT" => "00", "MERCHANT_RESPONSE_URL" => "https =>//www.example.com/response", "AUTHCODE" => "12345", "SHIPPING_CODE" => "654|123", "SHIPPING_CO" => "GB", "BILLING_CODE" => "50001", "BILLING_CO" => "US", "CARD_PAYMENT_BUTTON" => "Place Order", "AVSADDRESSRESULT" => "M", "AVSPOSTCODERESULT" => "M", "BATCHID" => "445196", "DCC_ENABLE" => "1", "HPP_FRAUDFILTER_MODE" => "PASSIVE", "HPP_LANG" => "EN", "MESSAGE" => "[ test system ] Authorised", "PASREF" => "15011597872195765", "CVNRESULT" => "M", "HPP_FRAUDFILTER_RESULT" => "PASS", "COMMENT1" => "Mobile Channel", "COMMENT2" => "Down Payment", "ECI" => "5", "XID" => "vJ9NXpFueXsAqeb4iAbJJbe+66s=", "CAVV" => "AAACBUGDZYYYIgGFGYNlAAAAAAA=", "CARDDIGITS" => "424242xxxx4242", "CARDTYPE" => "VISA", "EXPDATE" => "1025", "CHNAME" => "James Mason", "DCCRATE" => "1990", "HPP_CHOSEN_PMT_REF" => "099efeb4-eda2-4fd7-a04d-29647bb6c51d", "HPP_EDITED_PMT_REF" => "037bd26a-c76b-4ee4-8063-376d8858f23d", "HPP_DELETED_PMT_REF" => "3db4c72c-cd95-4743-8070-f17e2b56b642", "PAYER_SETUP_MSG" => "SUCCESS", "PMT_SETUP_MSG" => "SUCCESS", "SAVED_PMT_TYPE" => "VISA", "DCCCCP" => "fexco", "DCCRATE" => "1.7203", "DCCMERCHANTAMOUNT" => "1999", "DCCCARDHOLDERAMOUNT" => "3439", "DCCMERCHANTCURRENCY" => "EUR", "DCCCARDHOLDERCURRENCY" => "AUD", "DCCMARGINRATEPERCENTAGE" => "3.75", "DCCEXCHANGERATESOURCENAME" => "REUTERS WHOLESALE INTERBANK", "DCCCOMMISSIONPERCENTAGE" => "0", "DCCEXCHANGERATESOURCETIMESTAMP" => "20170518162700", "DCCCHOICE" => "Yes");

        try {
            $parsedResponse = $service->parseResponse($responseJson);
            $responseCode = $parsedResponse->responseCode; // 00
            $responseValues = $parsedResponse->responseValues; // get values accessible by key

            $conversionProcessor = $responseValues["DCCCCP"]; // fexco
            $conversionRate = $responseValues["DCCRATE"]; // 1.7203
            $merchantAmount = $responseValues["DCCMERCHANTAMOUNT"]; // 1999
            $cardholderAmount = $responseValues["DCCCARDHOLDERAMOUNT"]; // 3439
            $merchantCurrency = $responseValues["DCCMERCHANTCURRENCY"]; // EUR
            $cardholderCurrency = $responseValues["DCCCARDHOLDERCURRENCY"]; // AUD
            $marginPercentage = $responseValues["DCCMARGINRATEPERCENTAGE"]; // 3.75
            $exchangeSource = $responseValues["DCCEXCHANGERATESOURCENAME"]; // REUTERS WHOLESALE INTERBANK
            $commissionPercentage = $responseValues["DCCCOMMISSIONPERCENTAGE"]; // 0
            $exchangeTimestamp = $responseValues["DCCEXCHANGERATESOURCETIMESTAMP"]; // 20170518162700
            $dccChoice = $responseValues["DCCCHOICE"]; // Yes
            // TODO: update your application and display transaction outcome to the customer

            $this->assertNotEquals(null, $parsedResponse);
            $this->assertEquals("00", $responseCode);
        } catch (ApiException $e) {
            // For example if the SHA1HASH doesn't match what is expected
            // TODO: add your error handling here
        }
    }

}
