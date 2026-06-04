<?php

/**
 * Transit + TSEP Charge Endpoint
 *
 * Receives a single-use token from TSEP (client-side tokenization)
 * and processes a sale transaction through the Transit gateway.
 */

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\ServicesContainer;

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required input
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$amount = isset($_POST['Amount']) ? trim($_POST['Amount']) : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment token is required']);
    exit;
}

if (!is_numeric($amount) || floatval($amount) <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid amount is required']);
    exit;
}

// --- Transit Configuration ---
$config = new TransitConfig();
$config->merchantId     = '887000003226';
$config->username       = 'TA5622118';
$config->password       = 'f8mapGqWrE^rVaA9';
$config->deviceId       = '88700000322602';
$config->transactionKey = '2HZFSJ98G4XEGHXGP31IRLLG8H3XAWB2';
$config->developerId    = '003226G001';
$config->gatewayProvider = GatewayProvider::TRANSIT;
$config->acceptorConfig = new AcceptorConfig();

ServicesContainer::configureService($config);

// Use the single-use token from TSEP
$card = new CreditCardData();
$card->token = $token;

try {
    $response = $card->charge(floatval($amount))
        ->withCurrency('USD')
        ->withAllowDuplicates(true)
        ->execute();

    if ($response->responseCode === '00') {
        echo json_encode([
            'success' => true,
            'transactionId' => $response->transactionId,
            'responseCode' => $response->responseCode,
            'responseMessage' => $response->responseMessage ?? 'Approved'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Declined: ' . ($response->responseMessage ?? $response->responseCode)
        ]);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
