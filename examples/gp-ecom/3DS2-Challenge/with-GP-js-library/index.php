<?php
echo '<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Global Payments 3DS2 with challenge window Examples</title>
    <link rel="stylesheet"  href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="https://unpkg.com/globalpayments-3ds@1.8.5/dist/globalpayments-3ds.js"></script>
    <script src="main.js"></script>
</head>
<body>
<h2>Credit Card Form</h2>
<div class="row">
    <div class="col-sm-5">r
        <form id="form" action="charge.php" method="POST">
            <div class="form-group row">
                <label for="card-number" class="col-sm-2 control-label">Card Number</label>
                <div class="col-sm-10">
                    <input type="text" name="card-number" id="card-number" value="4012001038488884"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="securityCode" class="col-sm-2 control-label">Card Cvv</label>
                <div class="col-sm-10">
                    <input type="text" name="securityCode" id="securityCode" value="123" />
                </div>
            </div>
            <div class="form-group row">
                <label for="cardExpiration" class="col-sm-2 control-label">Card Expiration</label>
                <div class="col-sm-10">
                    <input type="text" name="cardExpiration" id="cardExpiration" value="12/26" />
                </div>
            </div>
            <div class="form-group row">
                <label for="cardHolderName" class="col-sm-2 control-label">Cardholder name</label>
                <div class="col-sm-10">
                    <input type="text" name="cardHolderName" id="cardHolderName" value="Jhon Smith" />
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10">
                    <button type="button" class="btn btn-success" name="startButton" id="startButton">Pay</button>
                </div>
            </div>

        </form>
    </div>
    <div class="col-sm-5">
        <iframe id="challenge" name="challenge" width="500" height="500" style="border: none"></iframe>
    </div>
</div>
</body>
</html>';