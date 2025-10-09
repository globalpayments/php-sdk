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

ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $_GET['token_value'];
$invoiceNumber = $_GET["invoice_number"];

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
        ->withInvoiceNumber($invoiceNumber)
        ->execute();

    $body = '<h1>Success!</h1>';
    $body .= '<p>Thank you, ' . $_GET['cardholder_name'] . ', for your order of $' . $_GET["payment_amount"] . '.</p>';

    echo "<b>Transaction Success! </b><br/> Transaction Id: " . $response->transactionId;
    echo "<br />Invoice Number: " . isset($invoiceNumber) ? htmlspecialchars($invoiceNumber) : "";

    // i'm running windows, so i had to update this:

    // sendEmail($_GET['EMAIL'], 'donotreply@e-hps.com', 'Successful Charge!', $body, true);
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
