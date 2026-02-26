<?php
/**
 * Visa Installment Transaction Examples for GP-API
 * 
 * This example demonstrates how to:
 * 1. Create a Visa installment transaction using the new VIS program
 * 2. Create a Visa installment plan with specific terms
 * 3. Query installment transactions
 * 4. Retrieve installment details
 */

require_once __DIR__ . '/../../autoload_standalone.php';

use GlobalPayments\Api\Entities\{Address, InstallmentData, InstallmentTerms};
use GlobalPayments\Api\PaymentMethods\{CreditCardData, Installment};
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\Entities\Enums\{Channel, Environment};

// Configure GP-API
$config = new GpApiConfig();
$config->appId = '60pS5h1X5M97lcBAnVBnWvk54EsqrjY3';
$config->appKey = 'Oku0xOOONJPbDWsd';
$config->channel = Channel::CardNotPresent;
$config->environment = Environment::TEST;
$config->country = 'US';

ServicesContainer::configureService($config);

echo "=== Visa Installment Examples ===\n\n";

// ============================================================================
// Example 1: Create a regular transaction with VIS program
// ============================================================================
echo "1. Create Transaction with VIS Program\n";
echo "---------------------------------------\n";

$card = new CreditCardData();
$card->number = "4263970000005262";  // Note: Remove letters from card number as per instructions
$card->expMonth = 12;
$card->expYear = 2026;
$card->cvn = "123";

$address = new Address();
$address->streetAddress1 = "123 Main St.";
$address->city = "Downtown";
$address->state = "NJ";
$address->postalCode = "12345";
$address->country = "US";

// Create installment data with VIS program
$installmentData = new InstallmentData();
$installmentData->program = "VIS";  // Visa installment program

try {
    $response = $card->charge(15.99)
        ->withCurrency("USD")
        ->withAddress($address)
        ->withInstallment($installmentData)
        ->withAllowDuplicates(true)
        ->execute();
    
    echo "Status: " . $response->responseCode . "\n";
    echo "Transaction ID: " . $response->transactionId . "\n";
    if (!empty($response->installment)) {
        echo "Installment Program: " . $response->installment->program . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 2: Create Visa Installment with Advanced Terms
// ============================================================================
echo "2. Create Visa Installment with Advanced Configuration\n";
echo "-------------------------------------------------------\n";

// Configure installment with Visa-specific fields
$installmentData = new InstallmentData();
$installmentData->program = "VIS";
$installmentData->funding_mode = "MERCHANT";  // or "ISSUER"

// Configure installment terms
$terms = new InstallmentTerms();
$terms->time_unit = "MONTH";
$terms->max_time_unit_number = 12;  // 12 month maximum
$terms->max_amount = "1000.00";     // Maximum amount per installment

$installmentData->terms = $terms;

// Optionally specify eligible plans
$installmentData->eligible_plans = ["PLAN_001", "PLAN_002"];

try {
    $response = $card->charge(599.99)
        ->withCurrency("USD")
        ->withAddress($address)
        ->withInstallment($installmentData)
        ->withAllowDuplicates(true)
        ->execute();
    
    echo "Status: " . $response->responseCode . "\n";
    echo "Transaction ID: " . $response->transactionId . "\n";
    if (!empty($response->installment)) {
        echo "Installment Program: " . $response->installment->program . "\n";
        if (!empty($response->installment->funding_mode)) {
            echo "Funding Mode: " . $response->installment->funding_mode . "\n";
        }
        if (!empty($response->installment->terms)) {
            echo "Terms - Time Unit: " . $response->installment->terms->time_unit . "\n";
            echo "Terms - Max Units: " . $response->installment->terms->max_time_unit_number . "\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 3: Create Installment Resource (Direct Installment Creation)
// ============================================================================
echo "3. Create Installment Resource\n";
echo "------------------------------\n";

$installment = new Installment();
$installment->accountName = "Transaction Processing";
$installment->amount = "299.99";
$installment->channel = Channel::CardNotPresent;
$installment->currency = "USD";
$installment->country = "US";
$installment->reference = "INV-" . time();
$installment->program = "VIS";
$installment->funding_mode = "ISSUER";
// Note: Entry mode should be set based on transaction context

// Set terms for the installment
$installmentTerms = new InstallmentTerms();
$installmentTerms->time_unit = "MONTH";
$installmentTerms->max_time_unit_number = 6;
$installmentTerms->max_amount = "50.00";

// Note: For direct installment creation, you would need to set card details
$cardData = new CreditCardData();
$cardData->number = "4263970000005262";
$cardData->expMonth = 12;
$cardData->expYear = 2026;

$installment->cardDetails = $cardData;

try {
    $createdInstallment = $installment->create();
    
    echo "Installment ID: " . $createdInstallment->id . "\n";
    echo "Status: " . $createdInstallment->status . "\n";
    echo "Program: " . $createdInstallment->program . "\n";
    echo "Amount: " . $createdInstallment->amount . "\n";
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 4: Query Installment Transactions
// ============================================================================
echo "4. Query Installment Transactions\n";
echo "----------------------------------\n";

try {
    $startDate = (new DateTime())->modify('-30 days');
    $endDate = new DateTime();
    
    $report = ReportingService::findTransactionsPaged(1, 10)
        ->withPaymentType("VIS")  // Filter by Visa installments
        ->withStartDate($startDate)
        ->withEndDate($endDate)
        ->execute();
    
    echo "Total Records: " . $report->totalRecordCount . "\n";
    echo "Transactions Found: " . count($report->result) . "\n";
    
    foreach ($report->result as $transaction) {
        echo "\nTransaction ID: " . $transaction->transactionId . "\n";
        echo "Amount: " . $transaction->amount . " " . $transaction->currency . "\n";
        if (!empty($transaction->installment)) {
            echo "Installment Program: " . $transaction->installment->program . "\n";
            if (!empty($transaction->installment->mode)) {
                echo "Installment Mode: " . $transaction->installment->mode . "\n";
            }
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 5: Get Installments (Paged)
// ============================================================================
echo "5. Get Installments (Paged Query)\n";
echo "----------------------------------\n";

try {
    $startDate = (new DateTime())->modify('-30 days');
    $endDate = new DateTime();
    
    $installmentReport = ReportingService::findInstallmentsPaged(1, 10)
        ->withStartDate($startDate)
        ->withEndDate($endDate)
        ->execute();
    
    echo "Total Installments: " . $installmentReport->totalRecordCount . "\n";
    echo "Records Found: " . count($installmentReport->result) . "\n";
    
    if (!empty($installmentReport->result)) {
        foreach ($installmentReport->result as $inst) {
            echo "\nInstallment ID: " . $inst->id . "\n";
            echo "Program: " . $inst->program . "\n";
            echo "Amount: " . $inst->amount . " " . $inst->currency . "\n";
            echo "Status: " . $inst->status . "\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Example 6: Get Installment Details by ID
// ============================================================================
echo "6. Get Installment Details\n";
echo "--------------------------\n";

// Note: Replace with actual installment ID from previous operations
$installmentId = "INS_123456789";  // Example ID

try {
    $installmentDetails = ReportingService::installmentDetail($installmentId)
        ->execute();
    
    echo "Installment ID: " . $installmentDetails->id . "\n";
    echo "Program: " . $installmentDetails->program . "\n";
    echo "Amount: " . $installmentDetails->amount . " " . $installmentDetails->currency . "\n";
    echo "Status: " . $installmentDetails->status . "\n";
    
    if (!empty($installmentDetails->funding_mode)) {
        echo "Funding Mode: " . $installmentDetails->funding_mode . "\n";
    }
    
    if (!empty($installmentDetails->terms)) {
        echo "Terms:\n";
        echo "  Time Unit: " . $installmentDetails->terms->time_unit . "\n";
        echo "  Max Time Units: " . $installmentDetails->terms->max_time_unit_number . "\n";
        echo "  Max Amount: " . $installmentDetails->terms->max_amount . "\n";
    }
    
    if (!empty($installmentDetails->eligible_plans)) {
        echo "Eligible Plans: " . implode(", ", $installmentDetails->eligible_plans) . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . " (Note: Use actual installment ID)\n\n";
}

// ============================================================================
// Example 7: HPP (Hosted Payment Page) with Visa Installments
// ============================================================================
echo "7. HPP with Visa Installments\n";
echo "------------------------------\n";
echo "Note: For HPP implementation with installments, refer to the\n";
echo "hosted payment page examples and include the installment\n";
echo "configuration in the HPP request.\n";
echo "\n";

echo "Example HPP Configuration with Installments:\n";
echo "```php\n";
echo "\$hppConfig = new HostedPaymentConfig();\n";
echo "// ... standard HPP configuration ...\n";
echo "\n";
echo "\$installmentData = new InstallmentData();\n";
echo "\$installmentData->program = 'VIS';\n";
echo "\$installmentData->funding_mode = 'MERCHANT';\n";
echo "// ... configure terms as needed ...\n";
echo "\n";
echo "// Include in authorization\n";
echo "\$builder->withInstallment(\$installmentData);\n";
echo "```\n\n";

echo "=== All Examples Completed ===\n";
