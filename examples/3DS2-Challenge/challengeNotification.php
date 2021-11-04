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

$decodedString = base64_decode($_POST['cres']);
echo "<script>
        window.parent.postMessage({ data: $decodedString}, window.location.origin);
    </script>";
