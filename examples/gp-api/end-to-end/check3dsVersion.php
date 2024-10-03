<?php
session_start();
require_once('../../../autoload_standalone.php');
require_once('GenerateToken.php');

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use \GlobalPayments\Api\Entities\Enums\Secure3dVersion;

// TODO: consume card data sent from the JS Library ($requestData)

$decodedData = json_decode(file_get_contents('php://input'));
$paymenttoken = $decodedData->tokenResponse;

// configure client & request settings
$config = new GpApiConfig();
$config->appId = GenerateToken::APP_ID;
$config->appKey = GenerateToken::APP_KEY;
$config->environment = Environment::TEST;
$config->country = 'GB';
$config->channel = Channel::CardNotPresent;
$config->methodNotificationUrl = $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/methodNotificationUrl.php';;
$config->merchantContactUrl = "https://www.example.com/about";
$config->challengeNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/challengeNotificationUrl.php';

ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $paymenttoken;

try {
   $threeDSecureData = Secure3dService::checkEnrollment($card)
      ->withCurrency("EUR")
      ->withAmount(100)
      ->execute();
} catch (ApiException $e) {
   // TODO: add your error handling here
   print_r($e);
}

$enrolled = $threeDSecureData->enrolled; // ENROLLED
// if enrolled, the available response data
$serverTransactionId = $threeDSecureData->serverTransactionId;
$messageVersion = $threeDSecureData->getVersion();
$methodUrl = $threeDSecureData->issuerAcsUrl;
$encodedMethodData = $threeDSecureData->payerAuthenticationRequest; // Base64 encoded string
$payerAuthenticationRequest = $threeDSecureData->payerAuthenticationRequest;
$issuerAcsUrl = $threeDSecureData->issuerAcsUrl;

// simple example of how to prepare the JSON string for JavaScript Library
$responseJson = array(
   "enrolled" => $enrolled,
   "messageVersion" => $messageVersion
);

if ($enrolled === "ENROLLED" && $messageVersion === Secure3dVersion::TWO) {
   $responseJson["serverTransactionId"] = $serverTransactionId;
   $responseJson["methodUrl"] = $methodUrl;
   $responseJson["methodData"] = $encodedMethodData;
}

$responseJson = json_encode($responseJson);

echo $responseJson;
