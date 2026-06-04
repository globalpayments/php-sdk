<?php
/**
 * Transit + TSEP End-to-End Example
 *
 * This page dynamically generates a TSEP manifest and renders a payment form
 * using the Global Payments JavaScript library for PCI-friendly tokenization.
 */

require_once('../../../autoload_standalone.php');

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\ServicesContainer;

// --- Transit Configuration ---
$merchantId     = '887000003226';
$username       = 'TA5622118';
// $password       = 'f8mapGqWrE^rVaA9';
$password = 'Hrcb^619';
$deviceId       = '88700000322602';
$developerId    = '003226G001';

$config = new TransitConfig();
$config->merchantId     = $merchantId;
$config->username       = $username;
$config->password       = $password;
$config->deviceId       = $deviceId;
$config->developerId    = $developerId;
$config->gatewayProvider = GatewayProvider::TRANSIT;
$config->acceptorConfig = new AcceptorConfig();
$config->transactionKey = '57ZL83P6A2V8KGI49QWK017C7WXG03O8'; 

ServicesContainer::configureService($config);

// Generate a transaction key and manifest for TSEP
$provider = ServicesContainer::instance()->getClient("default");
$manifest = $provider->createManifest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transit + TSEP Payment Example</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 500px; margin: 40px auto; padding: 0 20px; }
        h1 { font-size: 1.4em; }
        label { display: block; margin-top: 12px; font-weight: 600; font-size: 0.9em; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        #credit-card { margin-top: 16px; }
        #result { margin-top: 50px; padding: 12px; border-radius: 4px; display: none; }
        #result.success { display: block; background: #d4edda; color: #155724; }
        #result.error { display: block; background: #f8d7da; color: #721c24; }
        button { margin-top: 16px; padding: 10px 20px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
    </style>
</head>
<body>
    <h1>Transit + TSEP Payment Demo</h1>

    <form id="payment-form">
        <label for="FirstName">First Name</label>
        <input type="text" id="FirstName" name="FirstName" required>

        <label for="LastName">Last Name</label>
        <input type="text" id="LastName" name="LastName" required>

        <label for="Amount">Amount ($)</label>
        <input type="text" id="Amount" name="Amount" value="15.00" required>

        <!-- TSEP secure card fields -->
        <div id="credit-card"></div>
    </form>

    <div id="result"></div>

    <!-- Global Payments JS library for TSEP tokenization -->
    <script src="https://js.globalpay.com/v1/globalpayments.js"></script>
    <script>
        // Configure GlobalPayments JS for Transit / TSEP
        GlobalPayments.configure({
            deviceId: "<?php echo htmlspecialchars($deviceId, ENT_QUOTES, 'UTF-8'); ?>",
            manifest: "<?php echo htmlspecialchars($manifest, ENT_QUOTES, 'UTF-8'); ?>",
            env: "sandbox"
        });

        const resultDiv = document.getElementById("result");
        const submitBtn = document.getElementById("submit-btn");
        let tokenProcessed = false;

        const cardForm = GlobalPayments.creditCard.form("#credit-card");

        function processToken(token) {

            if (tokenProcessed) return;
            tokenProcessed = true;

            resultDiv.className = "";
            resultDiv.style.display = "block";
            resultDiv.textContent = "Processing payment...";

            const formData = new FormData(document.getElementById("payment-form"));
            formData.append("token", token);

            fetch("charge.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = "success";
                    resultDiv.textContent = "Payment successful! Transaction ID: " + data.transactionId;
                } else {
                    resultDiv.className = "error";
                    resultDiv.textContent = "Payment failed: " + data.message;
                }
            })
            .catch(() => {
                resultDiv.className = "error";
                resultDiv.textContent = "An unexpected error occurred.";
            })
            .finally(() => {
                tokenProcessed = false;
            });
        }

        // Standard library event
        cardForm.on("token-success", (resp) => {
            console.log("token-success fired", resp);
            processToken(resp.paymentReference);
        });

        cardForm.on("token-error", (resp) => {
            if (tokenProcessed) return;
            if (!resp || !resp.reasons || !resp.reasons.length) return;
            resultDiv.className = "error";
            resultDiv.style.display = "block";
            resultDiv.textContent = "Tokenization error: " + resp.reasons[0].message;
        });

        // Fallback: listen for TSEP postMessage token response directly
        window.addEventListener("message", (event) => {
            if (tokenProcessed) return;
            try {
                const data = typeof event.data === "string" ? JSON.parse(event.data) : event.data;
                // TSEP responses typically contain a token/paymentReference
                const token = data.paymentReference || data.token || data.temporary_token;
                if (token && typeof token === "string" && token.length > 10) {
                    console.log("TSEP token captured via postMessage", token);
                    processToken(token);
                }
            } catch (e) {
                // Not a JSON message we care about
            }
        });

        GlobalPayments.on("error", (error) => {
            if (tokenProcessed) return;
            console.error("GlobalPayments error", error);
            resultDiv.className = "error";
            resultDiv.style.display = "block";
            resultDiv.textContent = "Error: " + (error.reasons ? error.reasons[0].message : "Unknown error");
        });

        // Submit triggers tokenization via the library
        document.getElementById("payment-form").addEventListener("submit", (e) => {
            e.preventDefault();
            if (tokenProcessed) return;
            cardForm.submit();
        });
    </script>
</body>
</html>
