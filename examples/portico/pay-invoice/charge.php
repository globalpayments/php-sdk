<?php

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ'; #gitleaks:allow
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';

ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $_GET['payment-reference'];

$address = new Address();
$address->streetAddress1 = $_GET["address"];
$address->city = $_GET["city"];
$address->state = $_GET["state"];
$address->postalCode = preg_replace('/[^0-9]/', '', $_GET["billing-zip"]);
$address->country = "United States";

/*
  $validCardHolder = new HpsCardHolder();
  $validCardHolder->first-name = $_GET["first-name"];
  $validCardHolder->last-name = $_GET["last-name"];
  $validCardHolder->address = $address;
  $validCardHolder->phone-number = preg_replace('/[^0-9]/', '', $_GET["phone-number"]);
 */

try {
    $invoiceNumber = $_GET["invoice-number"];
    $response = $card->charge(15)
        ->withCurrency('USD')
        ->withAddress($address)
        ->withInvoiceNumber($invoiceNumber)
        ->withAllowDuplicates(true)
        ->execute();

    $body = '<h1>Success!</h1>';
    $body .= '<p>Thank you, ' . $_GET['first-name'] . ', for your order of $' . $_GET["payment-amount"] . '.</p>';

    echo "Transaction Id: " . $response->transactionId;
    echo "<br />Invoice Number: " . isset($invoiceNumber) ? htmlspecialchars($invoiceNumber) : "";

    // i'm running windows, so i had to update this:
    //ini_set("SMTP", "my-mail-server");

    // sendEmail($_GET['email'], 'donotreply@e-hps.com', 'Successful Charge!', $body, true);
} catch (Exception $e) {
    echo 'Failure: ' . $e->getMessage();
    exit;
}

function sendEmail($to, $from, $subject, $body, $isHtml)
{
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
