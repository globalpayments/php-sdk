<?php
require_once('../../vendor/autoload.php');

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Entities\HostedPaymentData;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Services\HostedService;

// configure client, request and HPP settings
$config = new GpEcomConfig();
$config->merchantId = "heartlandgpsandbox";
$config->accountId = "hpp";
$config->sharedSecret = "secret";
$config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";

$config->hostedPaymentConfig = new HostedPaymentConfig();
$config->hostedPaymentConfig->version = HppVersion::VERSION_2;
$service = new HostedService($config);

// Add 3D Secure 2 Mandatory and Recommended Fields
$hostedPaymentData = new HostedPaymentData();
$hostedPaymentData->customerEmail = "james.mason@example.com";
$hostedPaymentData->customerPhoneMobile = "44|07123456789";
$hostedPaymentData->addressesMatch = false;

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

try {
    $hppJson = $service->charge(19.99)
        ->withCurrency("EUR")
        ->withHostedPaymentData($hostedPaymentData)
        ->withAddress($billingAddress, AddressType::BILLING)
        ->withAddress($shippingAddress, AddressType::SHIPPING)
        ->serialize();
    //with this, we can pass our json to the client side
    echo $hppJson;
} catch (ApiException $e) {
    print_r($e);
    // TODO: Add your error handling here
}

