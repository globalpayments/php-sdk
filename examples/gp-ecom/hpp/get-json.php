<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Services\HostedService;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\HostedPaymentMethods;

// configure client, request and HPP settings
$config = new GpEcomConfig();
$config->merchantId = "openbankingsandbox";
$config->accountId = "internet";
$config->sharedSecret = "sharedsecret";
$config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";
$config->enableBankPayment = true;
$config->hostedPaymentConfig = new HostedPaymentConfig();
$config->hostedPaymentConfig->version = HppVersion::VERSION_2;

$service = new HostedService($config);

// Add 3D Secure 2 Mandatory and Recommended Fields
$hostedPaymentData = new HostedPaymentData();
$hostedPaymentData->customerEmail = "james.mason@example.com";
$hostedPaymentData->customerPhoneMobile = "44|07123456789";
$hostedPaymentData->addressesMatch = false;
if (isset($_REQUEST['captureAddress'])) {
    $hostedPaymentData->addressCapture = filter_var($_REQUEST['captureAddress'], FILTER_VALIDATE_BOOLEAN);
}
if (isset($_REQUEST['notReturnAddress'])) {
    $hostedPaymentData->notReturnAddress = filter_var($_REQUEST['notReturnAddress'], FILTER_VALIDATE_BOOLEAN);
}

$hostedPaymentData->customerCountry = 'GB';
$hostedPaymentData->customerFirstName = 'James';
$hostedPaymentData->customerLastName = 'Mason';
$hostedPaymentData->transactionStatusUrl = $_SERVER['HTTP_REFERER'] . '/examples/hpp/response-endpoint.php';
$hostedPaymentData->merchantResponseUrl = $_SERVER['HTTP_REFERER'] . '/examples/hpp/response-endpoint.php';
$hostedPaymentData->presetPaymentMethods = [HostedPaymentMethods::CARDS, HostedPaymentMethods::OB];

$billingAddress = new Address();
$billingAddress->streetAddress1 = "Flat 123";
$billingAddress->streetAddress2 = "House 456";
$billingAddress->streetAddress3 = "Unit 4";
$billingAddress->city = "Halifax";
$billingAddress->postalCode = "W5 9HR";
$billingAddress->country = "826";

$shippingAddress = new Address();
$shippingAddress->streetAddress1 = "Apartment 825";
$shippingAddress->streetAddress2 = "Complex 741";
$shippingAddress->streetAddress3 = "House 963";
$shippingAddress->city = "Chicago";
$shippingAddress->state = "IL";
$shippingAddress->postalCode = "50001";
$shippingAddress->country = "840";

$bankPayment = new BankPayment();
$bankPayment->accountNumber = '12345678';
$bankPayment->sortCode = '406650';
$bankPayment->accountName = 'AccountName';

$hostedPaymentData->bankPayment = $bankPayment;

try {
    $hppJson = $service->charge(19.99)
        ->withCurrency("GBP")
        ->withHostedPaymentData($hostedPaymentData)
        ->withAddress($billingAddress, AddressType::BILLING)
        ->withAddress($shippingAddress, AddressType::SHIPPING)
        ->withPhoneNumber('44', '124 445 556', PhoneNumberType::WORK)
        ->withPhoneNumber('44', '124 444 333', PhoneNumberType::HOME)
        ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
        ->serialize();
    //with this, we can pass our json to the client side
    echo $hppJson;
} catch (ApiException $e) {
    print_r($e);
    // TODO: Add your error handling here
}
