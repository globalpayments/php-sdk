<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

// configure client settings
$config = new GpEcomConfig();
/* Credentials for OpenBanking HPP
$config->merchantId = "openbankingsandbox";
$config->accountId = "internet";
$config->sharedSecret = "sharedsecret";
*/
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
if (!isset($_REQUEST['hppResponse'])) {
    $responseJson = json_encode($_REQUEST);
    $encoded = false;
} else {
    $responseJson = $_REQUEST['hppResponse'];
    $encoded = true;
}

try {
    // create the response object from the response JSON
    $parsedResponse = $service->parseResponse($responseJson, $encoded);

    $orderId = $parsedResponse->orderId; // GTI5Yxb0SumL_TkDMCAxQA
    $responseCode = $parsedResponse->responseCode; // 00
    $responseMessage = $parsedResponse->responseMessage; // [ test system ] Authorised
    $responseValues = $parsedResponse->responseValues; // get values accessible by key
    echo "<pre>";
    echo "Response Code : " . !empty($responseCode) ? htmlspecialchars($responseCode) : "";
    echo "\n Response Message : " . !empty($responseMessage) ? htmlspecialchars($responseMessage) : "";
    echo "\n Response Values : ";
    if (!empty($responseValues))
       print_r(array_map("htmlspecialchars", $responseValues));

} catch (ApiException $e) {
    print_r($e);
    // For example if the SHA1HASH doesn't match what is expected
    // TODO: add your error handling here
}
