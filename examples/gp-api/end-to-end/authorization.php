<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../autoload_standalone.php');
require_once('GenerateToken.php');

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

$requestData = $_REQUEST;
$serverTransactionId = $requestData['serverTransactionId'];
$paymentToken = $requestData['tokenResponse'];

console_log($serverTransactionId);
function console_log($data)
{
    $data = htmlspecialchars($data, ENT_NOQUOTES);
    echo '<script>';
    echo 'if(' . $data . ') {';
    echo 'console.log(' . json_encode($data) . ')';
    echo '}';
    echo '</script>';
}

// configure client & request settings
$config = new GpApiConfig();
$config->appId = GenerateToken::APP_ID;
$config->appKey = GenerateToken::APP_KEY;
$config->environment = Environment::TEST;
$config->country = 'GB';
$config->channel = Channel::CardNotPresent;
$config->merchantContactUrl = "https://www.example.com/about";
$config->methodNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/methodNotificationUrl.php';
$config->challengeNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/gp-api/end-to-end/challengeNotificationUrl.php';
ServicesContainer::configureService($config);

try {
    $secureEcom = Secure3dService::getAuthenticationData()
        ->withServerTransactionId($serverTransactionId)
        ->execute();
} catch (ApiException $e) {
    //TODO: Add your error handling here
    var_dump('Obtain Authentication error:', $e);
}

$authenticationValue = $secureEcom->authenticationValue;
$dsTransId = $secureEcom->directoryServerTransactionId;
$messageVersion = $secureEcom->messageVersion;
$eci = $secureEcom->eci;
?>
<!DOCTYPE html>
<html>

<head>
    <title>3D Secure 2 Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <h2>3D Secure 2 Authentication</h2>
    <?php
    $condition = ($secureEcom->liabilityShift != 'YES' ||
        !in_array(
            $secureEcom->status,
            [
                Secure3dStatus::SUCCESS_AUTHENTICATED,
                Secure3dStatus::SUCCESS_ATTEMPT_MADE
            ]
        ));
    if (empty($condition) && !$condition) {
        echo "<p><strong>Hurray! Your trasaction was authenticated successfully!</strong></p>";
    } else {
        echo "<p><strong>Oh Dear! Your trasaction was not authenticated successfully!</strong></p>";
    }
    ?>
    <p>Server Trans ID: <?= !empty($serverTransactionId) ? htmlspecialchars($serverTransactionId, ENT_NOQUOTES) : "" ?></p>
    <p>Authentication Value: <?= !empty($authenticationValue) ? $authenticationValue : "" ?></p>
    <p>DS Trans ID: <?= $dsTransId ?></p>
    <p>Message Version: <?= $messageVersion ?></p>
    <p>ECI: <?= $eci ?></p>

    <pre>
<?php
print_r(htmlspecialchars(json_encode($secureEcom), ENT_NOQUOTES));
?>
</pre>
    <h2>Transaction details:</h2>
    <?php
    if (!$condition) {
        $paymentMethod = new CreditCardData();
        $paymentMethod->token = $paymentToken;
        $paymentMethod->threeDSecure = $secureEcom;
        // proceed to authorization with liability shift
        try {
            $response = $paymentMethod->charge(100)
                ->withCurrency('EUR')
                ->execute();
        } catch (ApiException $e) {
            // TODO: Add your error handling here
            var_dump('Error message:', $e->getMessage());
        }
        if (!empty($response)) {
            $transactionId =  $response->transactionId;
            $transactionStatus =  $response->responseMessage;
        }
    }
    ?>
    <p>Trans ID: <?= $transactionId ?? null ?></p>
    <p>Trans status: <?= $transactionStatus ?? null ?></p>
    <pre>
<?php
if (!empty($response)) {
    print_r($response);
}
?>
</pre>