<?php
session_start();
require_once('../../../autoload_standalone.php');
require_once('GenerateToken.php');

use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\MerchantCategory;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;

$provider = $_GET['provider'] ?? '';

if ($provider === 'WeChat') {
    $provider = AlternativePaymentType::WECHAT_PAY;
} else {
    $provider = strtolower($provider);
}

// configure client & request settings
$config = new GpApiConfig();
$config->appId = GenerateToken::APP_ID;
$config->appKey = GenerateToken::APP_KEY;
$config->channel = Channel::CardNotPresent;
$config->country = 'HK';
$config->accessTokenInfo = new AccessTokenInfo();
$config->accessTokenInfo->transactionProcessingAccountID = GenerateToken::ACCOUNT_ID;
$config->requestLogger = new SampleRequestLogger(new Logger("logs"));
ServicesContainer::configureService($config);

$paymentMethod = new AlternativePaymentMethod($provider);
$paymentMethod->returnUrl = $_SERVER['HTTP_ORIGIN'] . '/examples/gp-api/alipay/returnUrl';
$paymentMethod->statusUpdateUrl = $_SERVER['HTTP_ORIGIN'] . '/examples/gp-api/alipay/returnUrl';
$paymentMethod->country = 'HK';
$paymentMethod->accountHolderName = 'Jane Doe';

try {
    $response = $paymentMethod->charge(19.99)
        ->withCurrency('HKD')
        ->withMerchantCategory(MerchantCategory::OTHER)
        ->execute();
} catch (ApiException $e) {
    // TODO: add your error handling here
    print_r($e);
}

// simple example of how to prepare the JSON string for JavaScript Library
$responseJson = array(
    "seconds_to_expire" => $response->alternativePaymentResponse->secondsToExpire ?? '120',
    "next_action" => $response->alternativePaymentResponse->nextAction ?? '',
    "redirect_url" => $response->alternativePaymentResponse->redirectUrl ?? '',
    "qr_code" => $response->alternativePaymentResponse->qrCodeImage ?? '',
    "provider" => $response->alternativePaymentResponse->providerName ?? ''
);

echo json_encode($responseJson);
