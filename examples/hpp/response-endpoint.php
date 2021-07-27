<?php
require_once('../../vendor/autoload.php');

use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

// configure client settings
$config = new GpEcomConfig();
$config->merchantId = "heartlandgpsandbox";
$config->accountId = "hpp";
$config->sharedSecret = "secret";
$config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";

$service = new HostedService($config);

/*
 * Response JSON comes from Global Payments
 * sample response JSON (values will be Base64 encoded):
 * $responseJson ='{"MERCHANT_ID":"MerchantId","ACCOUNT":"internet","ORDER_ID":"GTI5Yxb0SumL_TkDMCAxQA","AMOUNT":"1999",' .
 * '"TIMESTAMP":"20170725154824","SHA1HASH":"843680654f377bfa845387fdbace35acc9d95778","RESULT":"00","AUTHCODE":"12345",' .
 * '"CARD_PAYMENT_BUTTON":"Place Order","AVSADDRESSRESULT":"M","AVSPOSTCODERESULT":"M","BATCHID":"445196",' .
 * '"MESSAGE":"[ test system ] Authorised","PASREF":"15011597872195765","CVNRESULT":"M","HPP_FRAUDFILTER_RESULT":"PASS"}";
 */
$responseJson = $_POST['hppResponse'];
try {
    // create the response object from the response JSON
    $parsedResponse = $service->parseResponse($responseJson, true);

    $orderId = $parsedResponse->orderId; // GTI5Yxb0SumL_TkDMCAxQA
    $responseCode = $parsedResponse->responseCode; // 00
    $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
    $responseValues = $parsedResponse->responseValues; // get values accessible by key
    echo "<pre>";
    echo "Response Code : $responseCode \n";
    echo "Response Message : $responseMessage \n";
    echo "Response Values : ";
    print_r($responseValues);

} catch (ApiException $e) {
    print_r($e);
    // For example if the SHA1HASH doesn't match what is expected
    // TODO: add your error handling here
}