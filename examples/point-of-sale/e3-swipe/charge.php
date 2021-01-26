<?php
require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ';
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';

ServicesContainer::configureService($config);

$card = new CreditCardData();
$card->token = $_GET['securesubmit_token'];

try {
    $response = $card->charge(28.97)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

    $body = '<h1>Success!</h1>';
    $body .= '<p>Thank you, for your order of $15.</p>';

    echo "<b>Transaction Success your transaction Id is: </b>" . $response->transactionId;
} catch (Exception $e) {
    echo 'Failure: ' . $e->getMessage();
    exit;
}
?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SecureSubmit PHP end-to-end payment example using tokenization.">
        <meta name="author" content="Mark Hagan">
        <title>Simple Payment Form Demo</title>

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="assets/secure.submit-1.0.2.swipe.js"></script>

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <br />
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;Thank you for your order.</h1>
                    <h3>Order Id: <?php echo $response->transactionId ?></h3>
                    <p>
                        Your order has been processed and a reciept has been emailed to the account we have on file.
                    </p>
                </div>
            </div>

            <h1>What just happened?</h1>
            <ul class="list-group">
                <li class="list-group-item"><h3><span class="glyphicon glyphicon-credit-card"></span>&nbsp;The encrypted card data was collected from the reader.</h3></li>
                <li class="list-group-item"><h3><span class="glyphicon glyphicon-lock"></span>&nbsp;The encrypted data was sent directly to Heartland for tokenization.</h3></li>
                <li class="list-group-item"><h3><span class="glyphicon glyphicon-edit"></span>&nbsp;The corresponding token was added to the form as a hidden input.</h3></li>
                <li class="list-group-item"><h3><span class="glyphicon glyphicon-ok-circle"></span>&nbsp;The form was submitted and the token was charged.</h3></li>
            </ul>
            <p>An encrypted card was sent over an SSL, encrypted connection where it was tokenized. This token was then submitted to the merchant and charged. Yes please.</p>
        </div>
    </body>
</html>
