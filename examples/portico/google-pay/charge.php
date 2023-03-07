<?php

require_once ('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\PaymentDataSourceType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;

$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MVq4BQC5n3AAgd4M1Cvph2ud3CGaIclCgC7H_KxZaQ'; // 777703754644
ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $_POST['paymentReference'];
$card->paymentSource = PaymentDataSourceType::GOOGLEPAYWEB;

try {
    $response = $card->charge(15)
        ->withCurrency('USD')
        ->withAllowDuplicates(true)
        ->execute();

    echo "<h1>Success!</h1>" . PHP_EOL;

    echo "<b>Your transaction Id is: </b>" . $response->transactionId;
} catch (Exception $e) {
    echo 'Failure: ' . $e->getMessage();
    exit;
}