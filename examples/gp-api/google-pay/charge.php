<?php

require_once ('../.././../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

/**
 * The Google Pay token. If you have any input filtering on $_POST, you will need
 * to use htmlspecialchars_decode, otherwise this will not be a valid JSON anymore
 * and the API will throw an error
 */
$googlePayToken = htmlspecialchars_decode($_POST['googlePayToken']);

/**
 * Replace the '\\' with '\' so the encoded characters won't be decoded.
 * If your code adds extra backslashes ('\') the the string, you will need to manipulate it
 * in order to look like the one from test/Integration/Gateways/GpApiConnector/GpApiDigitalWalletTest.php
 */
$googlePayToken = str_replace('\\\\', '\\', $googlePayToken);

$config = new GpApiConfig();
$config->appId = 'i872l4VgZRtSrykvSn8Lkah8RE1jihvT';
$config->appKey = '9pArW2uWoA8enxKc';
$config->environment = Environment::TEST;
$config->channel = Channel::CardNotPresent;
$config->requestLogger = new SampleRequestLogger(new Logger("logs"));

ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $googlePayToken;
$card->mobileType = EncyptedMobileType::GOOGLE_PAY;

try {
    $transaction = $card->charge('10')
        ->withCurrency('GBP')
        ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
        ->execute();

    echo '<b>Transaction successful, your transaction id is: </b>' . $transaction->transactionId;
    echo '<br />';
    echo '<b>Transaction status: </b>' . $transaction->responseMessage;
} catch (\Exception $e) {
    echo 'Failure: ' . $e->getMessage();
    exit;
}
