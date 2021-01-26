<?php

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $_GET['token_value'];

$address = new Address();
$address->streetAddress1 = $_GET["Address"];
$address->city = $_GET["City"];
$address->state = $_GET["State"];
$address->postalCode = preg_replace('/[^0-9]/', '', $_GET["Zip"]);
$address->country = "United States";

try {
    $response = $card->charge(15)
        ->withCurrency('USD')
        ->withAddress($address)
        ->withAllowDuplicates(true)
        ->execute();

    // print_r($response);

    $body = '<h1>Success!</h1>';
    $body .= '<p>Thank you, ' . $_GET['FirstName'] . ', for your order of $15.</p>';

    echo "<b>Transaction Success your transaction Id is: </b>" . $response->transactionId;

    // i'm running windows, so i had to update this:
    //ini_set("SMTP", "my-mail-server");

    sendEmail($_GET['EMAIL'], 'donotreply@e-hps.com', 'Successful Charge!', $body, true);
} catch (Exception $e) {
    echo 'Failure: ' . $e->getMessage();
    exit;
}

function sendEmail($to, $from, $subject, $body, $isHtml) {
    $message = '<html><body>';
    $message .= $body;
    $message .= '</body></html>';

    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";

    if ($isHtml) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=ISO-8859-1\r\n";
    }

    mail($to, $subject, $message, $headers);
}
