<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\Secure3dService;

$config = new GpEcomConfig();
$config->merchantId = 'myMerchantId';
$config->accountId = 'ecom3ds';
$config->sharedSecret = 'secret';
$config->methodNotificationUrl = 'http://gp-sdk.localhost.com:8080/examples/3DS2-Challenge/methodNotificationUrl.php';
$config->challengeNotificationUrl = 'http://gp-sdk.localhost.com:8080/examples/3DS2-Challenge/challengeNotification.php';
$config->secure3dVersion = Secure3dVersion::ANY;
$config->merchantContactUrl = 'https://www.example.com';
ServicesContainer::configureService($config);

$requestData = json_decode(file_get_contents('php://input'));

$cardData = $requestData->card;
$card = new CreditCardData();
$card->number = $cardData->number;
$card->cvn = $cardData->securityCode;
$expDate = explode('/', $cardData->cardExpiration);
$card->expYear = $expDate[1];
$card->expMonth = $expDate[0];
$card->cardHolderName = $cardData->cardHolderName;
$config = ServicesContainer::instance()->getClient('default');

try {
    $threeDSecureData = Secure3dService::checkEnrollment($card)
        ->execute('default', Secure3dVersion::TWO);

    $response['enrolled']             = $threeDSecureData->enrolled;
    $response['version']              = $threeDSecureData->getVersion();
    $response['status']               = $threeDSecureData->status;
    $response['serverTransactionId']  = $threeDSecureData->serverTransactionId;

    if ($threeDSecureData->enrolled !== true) {
        return $response;
    }

    $response['methodUrl']   = $threeDSecureData->issuerAcsUrl;
    $response['methodData']  = $threeDSecureData->payerAuthenticationRequest;
} catch (\Exception $e) {
    $response = [
        'error'    => true,
        'message'  => $e->getMessage(),
        'enrolled' => 'NO_RESPONSE',
    ];
}
echo json_encode($response);
//print_r($response);