<?php

/**
 * This sample code is not specific to the Global Payments SDK and is intended as a simple example and
 * should not be treated as Production-ready code. You'll need to add your own message parsing and
 * security in line with your application or website
 */

/**
 * Merchant integration endpoint responsible for performing the authentication request
 */

require_once('../../../autoload_standalone.php');
require_once('GenerateToken.php');

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;

$requestData = json_decode(file_get_contents('php://input'));

$config = new GpApiConfig();
$config->appId = GenerateToken::APP_ID;
$config->appKey = GenerateToken::APP_KEY;
$config->environment = Environment::TEST;
$config->country = 'GB';
$config->channel = Channel::CardNotPresent;
$config->merchantContactUrl = "https://www.example.com/contact-us";
$config->methodNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/methodNotificationUrl.php';
$config->challengeNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/challengeNotificationUrl.php';

ServicesContainer::configureService($config);

$billingAddress = new Address();
$billingAddress->streetAddress1 = "Apartment 852";
$billingAddress->streetAddress2 = "Complex 741";
$billingAddress->streetAddress3 = "Unit 4";
$billingAddress->city = "Chicago";
$billingAddress->state = "IL";
$billingAddress->postalCode = "50001";
$billingAddress->countryCode = "840";

$shippingAddress = new Address();
$shippingAddress->streetAddress1 = "Flat 456";
$shippingAddress->streetAddress2 = "House 789";
$shippingAddress->streetAddress3 = "Basement Flat";
$shippingAddress->city = "Halifax";
$shippingAddress->postalCode = "W5 9HR";
$shippingAddress->countryCode = "826";

// TODO: Add captured browser data from the client-side and server-side
$browserData = new BrowserData();
$browserData->acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT']  : '';
$browserData->colorDepth = $requestData->browserData->colorDepth;
$browserData->ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']  : '';
$browserData->javaEnabled = $requestData->browserData->javaEnabled ?? false;
$browserData->javaScriptEnabled = $requestData->browserData->javascriptEnabled;
$browserData->language = $requestData->browserData->language;
$browserData->screenHeight = $requestData->browserData->screenHeight;
$browserData->screenWidth = $requestData->browserData->screenWidth;
$browserData->challengWindowSize = $requestData->challengeWindow->windowSize;
$browserData->timeZone = $requestData->browserData->timezoneOffset;
$browserData->userAgent = $requestData->browserData->userAgent;

$paymentMethod = new CreditCardData();
$paymentMethod->token = $requestData->tokenResponse;
$paymentMethod->cardHolderName = "James Mason";

$threeDSecureData = new ThreeDSecure();
$threeDSecureData->serverTransactionId = $requestData->serverTransactionId;
$methodUrlCompletion = MethodUrlCompletion::YES;
try {
    $threeDSecureData = Secure3dService::initiateAuthentication($paymentMethod, $threeDSecureData)
        ->withAmount($requestData->order->amount)
        ->withCurrency($requestData->order->currency)
        ->withOrderCreateDate(date('Y-m-d H:i:s'))
        ->withAddress($billingAddress, AddressType::BILLING)
        ->withAddress($shippingAddress, AddressType::SHIPPING)
        ->withAddressMatchIndicator(false)
        ->withAuthenticationSource($requestData->authenticationSource)
        ->withBrowserData($browserData)
        ->withMethodUrlCompletion($methodUrlCompletion)
        ->execute();
} catch (ApiException $e) {
    // TODO: add your error handling here
    print_r($e);
}

$status = $threeDSecureData->status;
$response = array();
$response['liabilityShift'] = $threeDSecureData->liabilityShift;

if ($status !== "CHALLENGE_REQUIRED") {
    // Frictionless flow
    $response['result'] = $threeDSecureData->status;
    $response['authenticationValue'] = $threeDSecureData->authenticationValue;
    $response['serverTransactionId'] = $threeDSecureData->serverTransactionId;
    $response['messageVersion'] = $threeDSecureData->messageVersion;
    $response['eci'] = $threeDSecureData->eci;
} else {
    //Challenge flow
    $response['status'] = $threeDSecureData->status; // CHALLENGE_REQUIRED
    $response['challengeMandated'] = $threeDSecureData->challengeMandated;
    $response['challenge']['requestUrl'] = $threeDSecureData->issuerAcsUrl;
    $response['challenge']['encodedChallengeRequest'] = $threeDSecureData->payerAuthenticationRequest;
    $response['challenge']['messageType'] = $threeDSecureData->messageType;
}

echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
