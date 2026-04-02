<?php

/**
 * Example of using the installments filtering feature with GlobalPayments PHP SDK via hosted payment pages
 */

require_once '../../../autoload_standalone.php';

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Enums\{Environment, Channel, CaptureMode, InstallmentsFundingMode, PhoneNumberType};
use GlobalPayments\Api\Entities\{PayerDetails, Address, PhoneNumber};

// Configuration for the GPApiConfig
$config = new GpApiConfig();
$config->appId = 'YOUR_APP_ID';
$config->appKey = 'YOUR_APP_KEY';
$config->environment = Environment::TEST;
$config->country = 'GB';
$config->channel = Channel::CardNotPresent;

ServicesContainer::configureService($config);

// Create payer details
$payer = new PayerDetails();
$payer->firstName = 'Nicolas';
$payer->lastName = 'Cage';
$payer->name = 'Nicolas Cage';
$payer->email = 'nicolas.cage@takepayments.com';
$payer->status = 'NEW';
$payer->mobilePhone = new PhoneNumber("44", "07900000000", PhoneNumberType::MOBILE);

// Create billing address
$payer->billingAddress = new Address();
$payer->billingAddress->streetAddress1 = 'Highbank house, Takepayments';
$payer->billingAddress->city = 'Stockport';
$payer->billingAddress->state = 'MAN';
$payer->billingAddress->postalCode = 'SK3 0ET';
$payer->billingAddress->country = 'GB';
$payer->billingAddress->countryCode = 'GB';

//This URL, minus the filename, is used to create return, status and cancel URLs
$demo_page_url = str_replace(
    "installments_example.php",
    "",
    "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
);

try {
    // Create hosted payment page with installments
    $response = HPPBuilder::create()
        ->withName('Installments Payment Example')
        ->withDescription('Payment with installment options')
        ->withReference('installments_payment_test_' . uniqid())
        ->withAmount('1000') // 1000 to trigger sandbox installments
        ->withCurrency('GBP')
        ->withPayer($payer)
        ->withBillingAddress($payer->billingAddress)
        ->withNotifications(
            $demo_page_url . "return_url.php",
            $demo_page_url . "status_url.php",
            $demo_page_url . "cancel_url.php"
        )
        ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
        // Configure installments with merchant funding, maximum is 24 months, and setting threshold of £10,000
        ->withInstallments(InstallmentsFundingMode::MERCHANT_FUNDED, '12', '100000')
        ->execute();

    $payByLinkUrl = (string) ($response->payByLinkResponse->url ?? '');
    $safePayByLinkUrl = htmlspecialchars($payByLinkUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    echo "<h1>Hosted Payment Page with installments filtering</h1>";
    echo '<p><a target="_blank" rel="noopener noreferrer" href="' . $safePayByLinkUrl . '">Click here to pay with installments</a></p>';
} catch (Exception $e) {
    echo "Error creating hosted payment page: " . $e->getMessage();
    echo "<pre>" . print_r($e->getTrace(), true) . "</pre>";
}
