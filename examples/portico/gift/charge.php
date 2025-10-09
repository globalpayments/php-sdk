<?php

require_once ('../../../autoload_standalone.php');

use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); #gitleaks:allow
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);


$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A'; #gitleaks:allow

ServicesContainer::configureService($config);

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
