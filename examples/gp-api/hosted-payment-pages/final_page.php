<h1>Transaction Results</h1>
<?php
function validate_request($gateway_response_json, $GP_signature) {
    if (!$gateway_response_json || !$GP_signature) {
        return false;
    }
    
    $parsed_data = json_decode($gateway_response_json, true);
    if (!$parsed_data) {
        error_log("Failed to parse gateway response JSON");
        return false;
    }
    if(isset($gateway_response_json['X-GP-Signature'])) {
        unset($gateway_response_json['X-GP-Signature']);
    }
    $minified_input = json_encode($parsed_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    return hash("sha512", $minified_input . "YOUR_APP_KEY") === $GP_signature;
}
$validation_result = validate_request($_POST['gateway_response'] ?? '', $_POST['X-GP-Signature'] ?? false);

if (!$validation_result) {
    http_response_code(403);
    die("Invalid Signature");
}
$gateway_data = json_decode($_POST['gateway_response'] ?? '{}', true);
echo "<h3>Gateway Response Data:</h3>";
echo "<pre>" . print_r($gateway_data, true) . "</pre>";
?>

