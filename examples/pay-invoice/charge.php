<?php

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$config = new ServicesConfig();
$config->secretApiKey = 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ';
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';

ServicesContainer::configure($config);

$card = new CreditCardData();
$card->token = $_GET['token_value'];

$address = new Address();
$address->streetAddress1 = $_GET["Address"];
$address->city = $_GET["City"];
$address->state = $_GET["State"];
$address->postalCode = preg_replace('/[^0-9]/', '', $_GET["Zip"]);
$address->country = "United States";

/*
  $validCardHolder = new HpsCardHolder();
  $validCardHolder->firstName = $_GET["FirstName"];
  $validCardHolder->lastName = $_GET["LastName"];
  $validCardHolder->address = $address;
  $validCardHolder->phoneNumber = preg_replace('/[^0-9]/', '', $_GET["PhoneNumber"]);
 */

try {
    $response = $card->charge(15)
            ->withCurrency('USD')
            ->withAddress($address)
            ->withInvoiceNumber($_GET["invoice_number"])
            ->withAllowDuplicates(true)
            ->execute();

    $body = '<h1>Success!</h1>';
    $body .= '<p>Thank you, ' . $_GET['FirstName'] . ', for your order of $' . $_GET["payment_amount"] . '.</p>';

    echo "Transaction Id: " . $response->transactionId;
    echo "<br />Invoice Number: " . $_GET["invoice_number"];

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
