<?php

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    return;
}
if ('application/x-www-form-urlencoded' !== $_SERVER['CONTENT_TYPE']) {
    return;
}

if (!isset($_POST['cres'])) {
    return;
}

$decodedString = base64_decode(strtr($_POST['cres'], '-_', '+/'), true);
if ($decodedString === false) {
    return;
}

$decodedData = json_decode($decodedString);
if (!is_object($decodedData)) {
    return;
}

$encodedData = json_encode($decodedData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
echo "<script>
        window.parent.postMessage({ data: " . $encodedData . " }, window.location.origin);
    </script>";
