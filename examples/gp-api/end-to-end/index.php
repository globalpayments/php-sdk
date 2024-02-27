<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once('GenerateToken.php');
    $accessToken = GenerateToken::getInstance()->getAccessToken();
?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Global Payments end-to-end with GP-API example</title>
    <link rel="stylesheet" href="styles.css" />
    <script src="https://js.globalpay.com/3.0.8/globalpayments.js"></script>
    <script src="globalpayments-3ds.js"></script>
    <script>
        let accessToken = "<?= $accessToken ?>";
    </script>
    <script defer src="main.js"></script>
</head>
<body>
    <div class="container">
        <p>3DS test card with CHALLENGE_REQUIRED: 4012 0010 3848 8884</p>
        <p>Amount: 100 EUR</p>
        <form id="payment-form" method="post">
            <!-- Target for the credit card form -->
            <div id="credit-card"></div>
        </form>
</div>
</body>
</html>
