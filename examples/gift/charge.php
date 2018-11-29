<?php

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);


$config = new ServicesConfig();
$config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';

ServicesContainer::configure($config);

try {
    $card = new GiftCard();
    $card->number = $_GET["card-number"];

    $response = $card->charge(1)
            ->withCurrency('USD')
            ->execute();

    //echo $response->responseCode;
    echo "Transaction success transaction Id: " . $response->transactionId;
} catch (Exception $e) {
    echo $e->getMessage();
}


