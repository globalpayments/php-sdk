<?php

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$config = new ServicesConfig();
$config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';
$config->versionNumber = '0000';
$config->developerId = '000000';

ServicesContainer::configure($config);

$address = new Address();
$address->address = $_POST['holder_address_address'];
$address->city = $_POST['holder_address_city'];
$address->state = $_POST['holder_address_state'];
$address->zip = $_POST['holder_address_zip'];

$eCheck = new ECheck();
$eCheck->accountNumber = $_POST['check_accountnumber'];
$eCheck->routingNumber = $_POST['check_routingnumber'];
$eCheck->checkType = $_POST['check_type'];
$eCheck->secCode = SecCode::WEB;
$eCheck->accountType = $_POST['account_type'];
$eCheck->entryMode = EntryMethod::MANUAL;
$eCheck->checkHolderName = 'John Doe';

try {
    $response = $eCheck->charge($_POST['payment_amount'])
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();

    printf('<b>Success! Transaction ID: %s</b>', $response->transactionId);
} catch (Exception $e) {
    printf('Error running check sale: %s', $e->getMessage());
    printf('<pre><code>%s</code></pre>', print_r($e, true));
}
