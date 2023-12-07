<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once('GenerateToken.php');
    $accessToken = GenerateToken::getInstance()->getAccessToken();
    $account = GenerateToken::ACCOUNT_ID;
?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Global Payments end-to-end with GP-API example</title>
    <link rel="stylesheet" href="styles.css" />
    <script src="https://js-cert.globalpay.com/2.1.2/globalpayments.js"></script>
    <script>
        let accessToken = "<?= $accessToken ?>";
        let account = "<?= $account ?>";
    </script>
    <script defer src="main.js"></script>
</head>
<body>
    <div class="container">
        <p>Amount: 100 EUR</p>
        <div id="digital-wallet-form"></div>
        <!-- Target for the credit card form -->
        <div id="credit-card"></div>
    </div>
</body>
</html>
