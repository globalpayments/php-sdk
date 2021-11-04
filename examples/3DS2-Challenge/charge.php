<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ('../../vendor/autoload.php');

use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\MethodUrlCompletion;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\ColorDepth;
use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Utils\CountryUtils;

// configure client & request settings
$config = new GpEcomConfig();
$config->merchantId = 'myMerchantId';
$config->accountId = 'ecom3ds';
$config->sharedSecret = 'secret';
$config->methodNotificationUrl = 'https://www.example.com/methodNotificationUrl';
$config->challengeNotificationUrl =  $_SERVER['HTTP_ORIGIN'] . '/examples/3DS2-Challenge/challengeNotification.php';
$config->secure3dVersion = Secure3dVersion::TWO;
$config->merchantContactUrl = 'https://www.example.com';

ServicesContainer::configureService($config);

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

// add cardholder data
$card = new CreditCardData();
$card->number = $_POST['cardNumber'];
$card->cvn = $_POST['cardCvv'];
$expDate = explode('/',$_POST['cardExpiration']);
$card->expYear = $expDate[1];
$card->expMonth = $expDate[0];
$card->cardHolderName = 'Me me';

if (!isset($_POST['ThreeDSData'])) {
    // Add the customer's billing address
    $shippingAddress = new Address();
    $shippingAddress->streetAddress1 = $_POST['address1'];
    $shippingAddress->streetAddress2 = $_POST['address2'];
    $shippingAddress->city = $_POST['city'];
    $shippingAddress->postalCode = $_POST['zip'];
    $shippingAddress->state = $_POST['state'];
    $shippingAddress->countryCode = CountryUtils::getNumericCodeByCountry($_POST['country']);

    // Add captured browser data from the client-side and server-side
    $browserData = new BrowserData();
    $browserData->acceptHeader = "text/html,application/xhtml+xml,application/xml;q=9,image/webp,img/apng,*/*;q=0.8";
    $browserData->colorDepth = ColorDepth::TWENTY_FOUR_BITS;
    $browserData->ipAddress = "123.123.123.123";
    $browserData->javaEnabled = true;
    $browserData->javaScriptEnabled = true;
    $browserData->language = "en";
    $browserData->screenHeight = 1080;
    $browserData->screenWidth = 1920;
    $browserData->challengWindowSize = ChallengeWindowSize::WINDOWED_600X400;
    $browserData->timeZone = "0";
    $browserData->userAgent = $_SERVER['HTTP_USER_AGENT'];
    try {
        $secureEcom = Secure3dService::checkEnrollment($card)
            ->execute('default', Secure3dVersion::TWO);

        if ($secureEcom->enrolled === true) {
            $secureEcom = Secure3dService::initiateAuthentication($card, $secureEcom)
                ->withAmount(10)
                ->withCurrency('USD')
                ->withAuthenticationSource(AuthenticationSource::BROWSER)
                ->withMethodUrlCompletion(MethodUrlCompletion::NO)
                ->withOrderCreateDate(date('Y-m-d H:i:s'))
                ->withAddress($shippingAddress, AddressType::SHIPPING)
                ->withAddress($shippingAddress, AddressType::BILLING)
                ->withBrowserData($browserData)
                ->execute();
            if ($secureEcom->status == "CHALLENGE_REQUIRED")
            {
                echo json_encode($secureEcom);
            } else {
                echo json_encode(['error' => 'true', 'status' => $secureEcom->status, 'message' => 'No challenge required']);
            }

        } else {
            echo json_encode(['error' => 'true', 'enrolled' => $secureEcom->enrolled, 'message' => 'Card not enrolled']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'true', 'message' => $e->getMessage()]);
    }
} else {
    try {
        $convertedObject = $_POST['ThreeDSData']['data'];

        $serverTransID = $convertedObject['threeDSServerTransID'];
        $acsTransID = $convertedObject['acsTransID'];
        $messageType = $convertedObject['messageType'];
        $messageVersion = $convertedObject['messageVersion'];
        $transStatus = $convertedObject['transStatus'];
        if ($transStatus == 'Y') {
            $secureEcom = Secure3dService::getAuthenticationData()
                ->withServerTransactionId($serverTransID)
                ->execute();
            // depending on the ECI value proceed to authorization
            if (in_array($secureEcom->eci, ["05", "06", "01", "02"])) {
                $card->threeDSecure = $secureEcom;
                $response = $card->charge(10)
                    ->withCurrency('USD')
                    ->withAllowDuplicates(true)
                    ->execute();
                echo json_encode($response);
            } else {
                echo 'Secure ecom status: ' . $secureEcom->status;
            }
        }

    } catch (Exception $exce) {
        echo 'Fail:' . $exce->getMessage();
        // TODO: Add your exception handling here
    }
}