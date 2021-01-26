<?php

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Entities\Enums\PaymentSchedule;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

$config = new PorticoConfig();
$config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
$config->serviceUrl = 'https://cert.api2.heartlandportico.com';

ServicesContainer::configureService($config);

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

function SendEmail($to, $from, $subject, $body, $isHtml) {
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

function getIdentifier($id) {
    $identifierBase = '%s-%s' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);
    return sprintf($identifierBase, date('Ymd'), $id);
}

function createCustomer() {
    $customer = new Customer();
    $customer->id = getIdentifier('Person');
    $customer->firstName = $_GET["FirstName"];
    $customer->lastName = $_GET["LastName"];
    $customer->status = 'Active';
    $customer->email = $_GET['Email'];
    $customer->address = new Address();
    $customer->address->streetAddress1 = $_GET["Address"];
    $customer->address->city = $_GET["City"];
    $customer->address->province = $_GET["State"];
    $customer->address->postalCode = $_GET["Zip"];
    $customer->address->country = 'USA';
    $customer->workPhone = $_GET["PhoneNumber"];

    $newCustomer = $customer->create();

    return $newCustomer;
}

function createPaymentMethod($customer) {
    $card = new CreditCardData();
    $card->token = $_GET['token_value'];

    $paymentMethod = $customer->addPaymentMethod(
                    getIdentifier('CreditV'), $card
            )->create();

    return $paymentMethod;
}

function createSchedule($customerKey, $paymentMethodKey, $amount) {
    $schedule = new Schedule();

    $schedule->id = getIdentifier('CreditV');
    $schedule->customerKey = $customerKey;
    $schedule->paymentKey = $paymentMethodKey;
    $schedule->amount = $amount;
    $schedule->currency = 'USD';
    $schedule->startDate = new DateTime("last day of +1 month");
    $schedule->paymentSchedule = PaymentSchedule::LAST_DAY_OF_THE_MONTH; //or PaymentSchedule::FIRST_DAY_OF_THE_MONTH
    $schedule->frequency = ScheduleFrequency::MONTHLY; //'Monthly', 'Bi-Monthly', 'Quarterly', 'Semi-Annually'
    //$schedule->duration = HpsPayPlanScheduleDuration::ONGOING;
    $schedule->reprocessingCount = 1;
    $schedule->emailReceipt = 'Never';
    $schedule->status = 'Active';
    $schedule->reprocessingCount = 1;

    $response = $schedule->create();

    return $response;
}

try {
    $customer = createCustomer();
    $paymentMethod = createPaymentMethod($customer);
    $schedule = createSchedule($customer->key, $paymentMethod->key, $_GET['payment_amount']);

    echo '<b>Your payment scheduled successfully </b><br />';
    printf('Customer Key: %s<br />', $customer->key);
    printf('Payment Method Key: %s<br />', $paymentMethod->key);
    printf('Schedule Key: %s<br />', $schedule->key);
} catch (Exception $e) {
    die($e->getMessage());
}


$body = '<h1>Success!</h1>';
$body .= '<p>Thank you, ' . $_GET['FirstName'] . ', for your subscription.';



// i'm running windows, so i had to update this:
//ini_set("SMTP", "my-mail-server");

//SendEmail($_GET['Email'], 'donotreply@e-hps.com', 'Successful Charge!', $body, true);
