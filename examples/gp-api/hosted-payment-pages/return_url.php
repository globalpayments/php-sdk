<?php

$GP_signature = getallheaders()['X-GP-Signature'] ?? false;
$raw_input = trim(file_get_contents('php://input')) ?? false;
$input = json_decode($raw_input, true); 

function validate_request($raw_input, $GP_signature) {
    if (!$raw_input || !$GP_signature) {
        error_log("X-GP-Signature header not found, or no post data");
        return false;
    }
    $parsed_input = json_decode($raw_input, true);
    if (!$parsed_input) {
        error_log("Failed to parse JSON input");
        return false;
    }
    $minified_input = json_encode($parsed_input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    return hash("sha512", $minified_input . "YOUR_APP_KEY") === $GP_signature;
}

// Validate the request signature
$is_valid_request = validate_request($raw_input, $GP_signature);

if (!$is_valid_request) {
    http_response_code(403);
    die("Invalid Request");
}

//Please note that this javascript is rendered on the external payment form page, 
// any 404 405 errors will be down to the server configuration.
?>
<h1>Return URL</h1>
<script>
    const form = document.createElement("form");
    form.method = "POST";
    form.id = "paymentForm";
    form.action = "<?= str_replace("return_url", "final_page", "https://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])?>"; // Change this to your final processing URL

    //Include the signature in the POST request, so it can be verified on the again final page.
    const signatureKey = document.createElement("input");
    signatureKey.type = "hidden";
    signatureKey.name = "X-GP-Signature";
    signatureKey.value = <?php echo json_encode($GP_signature, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    form.appendChild(signatureKey);

    const gatewayResponse = document.createElement("input");
    gatewayResponse.type = "hidden";
    gatewayResponse.name = "gateway_response";
    gatewayResponse.value = JSON.stringify(<?php echo json_encode($input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>);
    form.appendChild(gatewayResponse);
    
    document.body.appendChild(form);
    form.submit();
</script>;